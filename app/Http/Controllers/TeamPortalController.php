<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Challenge;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TeamPortalController extends Controller
{
    /**
     * Show login page
     */
    public function showLogin()
    {
        return view('team.login');
    }

    /**
     * Handle team login
     */
    public function login(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $team = Team::where('access_token', $request->token)->first();

        if (!$team) {
            return back()->withErrors(['token' => 'Invalid team token']);
        }

        Session::put('team_id', $team->id);
        Session::put('team_name', $team->name);

        return redirect()->route('team.dashboard');
    }

    /**
     * Show team dashboard
     */
    public function dashboard()
    {
        $teamId = Session::get('team_id');
        if (!$teamId) {
            return redirect()->route('team.login');
        }

        $team = Team::find($teamId);
        $currentPhase = $this->getCurrentPhase($team);
        
        // Get challenges for current phase
        $challenges = Challenge::where('phase', $currentPhase)
            ->where('is_active', true)
            ->orderBy('order_in_phase')
            ->get()
            ->map(function ($challenge) use ($team) {
                $submission = Submission::where('team_id', $team->id)
                    ->where('challenge_id', $challenge->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $challenge->id,
                    'name' => $challenge->name,
                    'description' => $challenge->description,
                    'points' => $challenge->points,
                    'status' => $submission ? $submission->status : 'not_attempted',
                    'last_submission' => $submission,
                    'passed' => $submission && $submission->status === 'passed'
                ];
            });

        // Get phase progress
        $phaseProgress = $team->getPhaseProgress();
        $totalScore = $team->total_score;

        // Calculate time remaining in phase
        $hackathonStart = Session::get('hackathon_start', now());
        $elapsed = now()->diffInMinutes($hackathonStart);
        
        $phaseInfo = $this->getPhaseInfo($currentPhase, $elapsed);

        return view('team.dashboard', compact(
            'team', 
            'challenges', 
            'currentPhase', 
            'phaseProgress', 
            'totalScore',
            'phaseInfo'
        ));
    }

    /**
     * Submit solution via web interface
     */
    public function submitSolution(Request $request)
    {
        $teamId = Session::get('team_id');
        if (!$teamId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $request->validate([
            'challenge_id' => 'required|exists:challenges,id',
            'code' => 'required|string'
        ]);

        $team = Team::find($teamId);
        $challenge = Challenge::find($request->challenge_id);

        // Check if already passed
        $existingPass = Submission::where('team_id', $team->id)
            ->where('challenge_id', $challenge->id)
            ->where('status', 'passed')
            ->exists();

        if ($existingPass) {
            return response()->json([
                'status' => 'already_passed',
                'message' => 'You have already completed this challenge!'
            ]);
        }

        // Create submission
        $submission = Submission::create([
            'team_id' => $team->id,
            'challenge_id' => $challenge->id,
            'code' => $request->code,
            'status' => 'testing'
        ]);

        // Run tests (in production, queue this)
        $this->runTest($submission);

        return response()->json([
            'status' => $submission->status,
            'message' => $submission->status === 'passed' ? 
                'Challenge completed! +' . $challenge->points . ' points!' : 
                'Test failed. Try again!',
            'output' => $submission->test_output,
            'error' => $submission->error_message
        ]);
    }

    /**
     * Logout team
     */
    public function logout()
    {
        Session::forget(['team_id', 'team_name']);
        return redirect()->route('team.login');
    }

    /**
     * Helper methods
     */
    private function getCurrentPhase(Team $team)
    {
        $phases = ['warmup', 'momentum', 'deepdive', 'finale'];
        $requiredCompletions = [
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

            if ($completed < $requiredCompletions[$phase]) {
                return $phase;
            }
        }

        return 'finale';
    }

    private function getPhaseInfo($phase, $elapsedMinutes)
    {
        $phases = [
            'warmup' => ['start' => 10, 'end' => 30, 'name' => 'Warm-up'],
            'momentum' => ['start' => 30, 'end' => 60, 'name' => 'Momentum'],
            'deepdive' => ['start' => 60, 'end' => 100, 'name' => 'Deep Dive'],
            'finale' => ['start' => 100, 'end' => 120, 'name' => 'Finale']
        ];

        $info = $phases[$phase] ?? $phases['warmup'];
        $info['time_remaining'] = max(0, $info['end'] - $elapsedMinutes);
        
        return $info;
    }

    private function runTest(Submission $submission)
    {
        try {
            $filename = 'submission_' . $submission->id . '.py';
            $filepath = storage_path('app/submissions/' . $filename);
            
            file_put_contents($filepath, $submission->code);

            // Get test script path
            $testScript = $submission->challenge->test_script;
            
            // Simple test execution (improve with Docker in production)
            $command = "cd " . storage_path('app/tests') . " && python3 $testScript $filepath 2>&1";
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            $outputStr = implode("\n", $output);

            $submission->update([
                'status' => $returnCode === 0 ? 'passed' : 'failed',
                'test_output' => $outputStr,
                'error_message' => $returnCode !== 0 ? 'Test failed' : null
            ]);

            unlink($filepath);

        } catch (\Exception $e) {
            $submission->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}