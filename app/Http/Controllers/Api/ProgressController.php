<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Challenge;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /**
     * Get current progress for all teams in TouchDesigner format
     */
    public function getProgress()
    {
        $teams = Team::all();
        $teamsData = [];

        foreach ($teams as $team) {
            // Get completed challenges count per phase
            $phaseProgress = DB::table('submissions')
                ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
                ->where('submissions.team_id', $team->id)
                ->where('submissions.status', 'passed')
                ->select('challenges.phase', DB::raw('COUNT(DISTINCT challenges.id) as completed'))
                ->groupBy('challenges.phase')
                ->pluck('completed', 'phase')
                ->toArray();

            // Check if team completed finale (for finalist status)
            $finaleCompleted = isset($phaseProgress['finale']) && $phaseProgress['finale'] > 0;
            
            // Update finalist status if completed finale
            if ($finaleCompleted && !$team->is_finalist) {
                $team->update(['is_finalist' => true]);
            }

            $teamsData[] = [
                'name' => $team->name,
                'phases' => [
                    'warmup' => $phaseProgress['warmup'] ?? 0,
                    'momentum' => $phaseProgress['momentum'] ?? 0,
                    'deepdive' => $phaseProgress['deepdive'] ?? 0,
                    'finale' => $finaleCompleted ? 1 : 0
                ]
            ];
        }

        return response()->json([
            'teams' => $teamsData
        ]);
    }

    /**
     * Submit a solution for testing
     */
    public function submitSolution(Request $request)
    {
        $request->validate([
            'team_token' => 'required|string',
            'challenge_id' => 'required|exists:challenges,id',
            'code' => 'required|string'
        ]);

        $team = Team::where('access_token', $request->team_token)->firstOrFail();
        $challenge = Challenge::findOrFail($request->challenge_id);

        // Check if team already passed this challenge
        $existingPass = Submission::where('team_id', $team->id)
            ->where('challenge_id', $challenge->id)
            ->where('status', 'passed')
            ->exists();

        if ($existingPass) {
            return response()->json([
                'message' => 'Already completed this challenge',
                'status' => 'already_passed'
            ], 200);
        }

        // Create submission
        $submission = Submission::create([
            'team_id' => $team->id,
            'challenge_id' => $challenge->id,
            'code' => $request->code,
            'status' => 'pending'
        ]);

        // Queue test execution (or run synchronously for demo)
        $this->runTest($submission);

        return response()->json([
            'submission_id' => $submission->id,
            'status' => $submission->status,
            'message' => $submission->status === 'passed' ? 'Challenge completed!' : 'Test failed',
            'error' => $submission->error_message
        ]);
    }

    /**
     * Get available challenges for a team
     */
    public function getChallenges(Request $request)
    {
        $request->validate([
            'team_token' => 'required|string'
        ]);

        $team = Team::where('access_token', $request->team_token)->firstOrFail();

        // Get current phase based on completed challenges
        $currentPhase = $this->getCurrentPhase($team);

        // Get challenges with completion status
        $challenges = Challenge::where('is_active', true)
            ->where('phase', $currentPhase)
            ->get()
            ->map(function ($challenge) use ($team) {
                $passed = Submission::where('team_id', $team->id)
                    ->where('challenge_id', $challenge->id)
                    ->where('status', 'passed')
                    ->exists();

                return [
                    'id' => $challenge->id,
                    'name' => $challenge->name,
                    'description' => $challenge->description,
                    'phase' => $challenge->phase,
                    'points' => $challenge->points,
                    'completed' => $passed
                ];
            });

        return response()->json([
            'current_phase' => $currentPhase,
            'challenges' => $challenges
        ]);
    }

    /**
     * Run test for a submission (simplified version)
     */
    private function runTest(Submission $submission)
    {
        $submission->update(['status' => 'testing']);

        try {
            // Save code to temporary file
            $filename = 'submission_' . $submission->id . '.py';
            $filepath = storage_path('app/submissions/' . $filename);
            
            if (!file_exists(storage_path('app/submissions'))) {
                mkdir(storage_path('app/submissions'), 0755, true);
            }
            
            file_put_contents($filepath, $submission->code);

            // Run test script (simplified - in production use Docker)
            $testScript = $submission->challenge->test_script;
            $testCommand = str_replace('${SUBMISSION_FILE}', $filepath, $testScript);
            
            $output = [];
            $returnCode = 0;
            exec($testCommand . ' 2>&1', $output, $returnCode);
            
            $outputStr = implode("\n", $output);

            if ($returnCode === 0) {
                // Check expected output if defined
                if ($submission->challenge->expected_output) {
                    $passed = trim($outputStr) === trim($submission->challenge->expected_output);
                } else {
                    $passed = true; // Test script handles validation
                }

                $submission->update([
                    'status' => $passed ? 'passed' : 'failed',
                    'test_output' => $outputStr,
                    'error_message' => $passed ? null : 'Output mismatch'
                ]);
            } else {
                $submission->update([
                    'status' => 'failed',
                    'test_output' => $outputStr,
                    'error_message' => 'Test execution failed'
                ]);
            }

            // Cleanup
            unlink($filepath);

        } catch (\Exception $e) {
            $submission->update([
                'status' => 'failed',
                'error_message' => 'System error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Determine current phase for a team based on completions
     */
    private function getCurrentPhase(Team $team)
    {
        $phases = ['warmup', 'momentum', 'deepdive', 'finale'];
        $phaseChallenges = [
            'warmup' => 2,
            'momentum' => 2,
            'deepdive' => 2,
            'finale' => 1
        ];

        foreach ($phases as $phase) {
            $completed = Submission::join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
                ->where('submissions.team_id', $team->id)
                ->where('submissions.status', 'passed')
                ->where('challenges.phase', $phase)
                ->count();

            if ($completed < $phaseChallenges[$phase]) {
                return $phase;
            }
        }

        return 'finale'; // All phases completed
    }

    /**
     * Admin endpoint to manually update progress
     */
    public function updateProgress(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'challenge_id' => 'required|exists:challenges,id',
            'passed' => 'required|boolean'
        ]);

        if ($request->passed) {
            Submission::updateOrCreate(
                [
                    'team_id' => $request->team_id,
                    'challenge_id' => $request->challenge_id
                ],
                [
                    'code' => 'Manual entry',
                    'status' => 'passed',
                    'test_output' => 'Manually marked as passed'
                ]
            );
        } else {
            Submission::where('team_id', $request->team_id)
                ->where('challenge_id', $request->challenge_id)
                ->where('status', 'passed')
                ->delete();
        }

        return response()->json(['message' => 'Progress updated']);
    }
}