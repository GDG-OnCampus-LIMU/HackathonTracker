<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto p-8">
        <h1 class="text-4xl font-bold mb-8 text-center">Hackathon Progress Dashboard</h1>
        
        <!-- Team Cards -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-red-900 p-6 rounded-lg" id="team-red">
                <h2 class="text-2xl font-bold mb-4">Team Red</h2>
                <div class="progress-bars"></div>
                <div class="score text-3xl font-bold mt-4">Score: 0</div>
            </div>
            
            <div class="bg-blue-900 p-6 rounded-lg" id="team-blue">
                <h2 class="text-2xl font-bold mb-4">Team Blue</h2>
                <div class="progress-bars"></div>
                <div class="score text-3xl font-bold mt-4">Score: 0</div>
            </div>
            
            <div class="bg-green-900 p-6 rounded-lg" id="team-green">
                <h2 class="text-2xl font-bold mb-4">Team Green</h2>
                <div class="progress-bars"></div>
                <div class="score text-3xl font-bold mt-4">Score: 0</div>
            </div>
            
            <div class="bg-yellow-900 p-6 rounded-lg" id="team-yellow">
                <h2 class="text-2xl font-bold mb-4">Team Yellow</h2>
                <div class="progress-bars"></div>
                <div class="score text-3xl font-bold mt-4">Score: 0</div>
            </div>
        </div>
        
        <!-- Timer -->
        <div class="text-center mb-8">
            <div class="text-6xl font-mono" id="timer">00:00:00</div>
            <div class="text-xl mt-2" id="phase-indicator">Phase: Warmup</div>
        </div>
        
        <!-- Controls -->
        <div class="flex justify-center gap-4">
            <button onclick="startHackathon()" class="bg-green-600 px-6 py-3 rounded-lg hover:bg-green-700">
                Start Hackathon
            </button>
            <button onclick="pauseHackathon()" class="bg-yellow-600 px-6 py-3 rounded-lg hover:bg-yellow-700">
                Pause
            </button>
            <button onclick="resetHackathon()" class="bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700">
                Reset
            </button>
        </div>
    </div>

    <script>
        let startTime = null;
        let timerInterval = null;
        let progressInterval = null;
        
        const phases = [
            { name: 'Warmup', start: 10, end: 30 },
            { name: 'Momentum', start: 30, end: 60 },
            { name: 'Deep Dive', start: 60, end: 100 },
            { name: 'Finale', start: 100, end: 120 }
        ];
        
        function updateProgress() {
            axios.get('/api/progress')
                .then(response => {
                    const teams = response.data.teams;
                    teams.forEach(team => {
                        updateTeamDisplay(team);
                    });
                })
                .catch(error => console.error('Error fetching progress:', error));
        }
        
        function updateTeamDisplay(team) {
            const teamName = team.name.toLowerCase().replace(' ', '-');
            const teamElement = document.getElementById(teamName);
            if (!teamElement) return;
            
            const progressBars = teamElement.querySelector('.progress-bars');
            const scoreElement = teamElement.querySelector('.score');
            
            // Calculate score
            const score = team.phases.warmup * 10 + 
                         team.phases.momentum * 20 + 
                         team.phases.deepdive * 30;
            
            scoreElement.textContent = `Score: ${score}`;
            
            // Update progress bars
            progressBars.innerHTML = `
                <div class="mb-2">
                    <div class="text-sm">Warmup: ${team.phases.warmup}/2</div>
                    <div class="bg-gray-700 h-2 rounded">
                        <div class="bg-red-500 h-2 rounded" style="width: ${team.phases.warmup * 50}%"></div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-sm">Momentum: ${team.phases.momentum}/2</div>
                    <div class="bg-gray-700 h-2 rounded">
                        <div class="bg-yellow-500 h-2 rounded" style="width: ${team.phases.momentum * 50}%"></div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-sm">Deep Dive: ${team.phases.deepdive}/2</div>
                    <div class="bg-gray-700 h-2 rounded">
                        <div class="bg-blue-500 h-2 rounded" style="width: ${team.phases.deepdive * 50}%"></div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-sm">Finale: ${team.phases.finale}/1</div>
                    <div class="bg-gray-700 h-2 rounded">
                        <div class="bg-green-500 h-2 rounded" style="width: ${team.phases.finale * 100}%"></div>
                    </div>
                </div>
            `;
            
            // Add finalist glow effect
            if (team.phases.finale === 1) {
                teamElement.classList.add('ring-4', 'ring-yellow-400', 'ring-opacity-50');
            }
        }
        
        function updateTimer() {
            if (!startTime) return;
            
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            const hours = Math.floor(minutes / 60);
            
            document.getElementById('timer').textContent = 
                `${String(hours).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            // Update phase indicator
            const currentPhase = phases.find(p => minutes >= p.start && minutes < p.end);
            if (currentPhase) {
                document.getElementById('phase-indicator').textContent = `Phase: ${currentPhase.name}`;
            } else if (minutes >= 120) {
                document.getElementById('phase-indicator').textContent = 'Hackathon Complete!';
            }
        }
        
        function startHackathon() {
            if (!startTime) {
                startTime = Date.now();
            }
            timerInterval = setInterval(updateTimer, 1000);
            progressInterval = setInterval(updateProgress, 3000);
            updateProgress();
        }
        
        function pauseHackathon() {
            clearInterval(timerInterval);
            clearInterval(progressInterval);
        }
        
        function resetHackathon() {
            if (confirm('Are you sure you want to reset the hackathon?')) {
                startTime = null;
                clearInterval(timerInterval);
                clearInterval(progressInterval);
                document.getElementById('timer').textContent = '00:00:00';
                document.getElementById('phase-indicator').textContent = 'Phase: Not Started';
            }
        }
        
        // Initial load
        updateProgress();
    </script>
</body>
</html>