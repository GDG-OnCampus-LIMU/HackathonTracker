<!-- resources/views/team/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $team->name }} - Hackathon Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-900 text-white">
    <!-- Header -->
    <header class="bg-gray-800 shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-{{ strtolower(str_replace('Team ', '', $team->name)) }}-600 flex items-center justify-center">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $team->name }}</h1>
                        <p class="text-sm text-gray-400">Score: {{ $totalScore }} points</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-400">Current Phase</p>
                        <p class="text-xl font-bold text-{{ 
                            $currentPhase == 'warmup' ? 'red' : 
                            ($currentPhase == 'momentum' ? 'yellow' : 
                            ($currentPhase == 'deepdive' ? 'blue' : 'green')) 
                        }}-400">{{ $phaseInfo['name'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-400">Time Remaining</p>
                        <p class="text-xl font-mono">{{ $phaseInfo['time_remaining'] }} min</p>
                    </div>
                    <a href="{{ route('team.logout') }}" class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Progress Bar -->
    <div class="bg-gray-800 py-4">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-sm mb-2">Warmup ({{ $phaseProgress['warmup'] ?? 0 }}/2)</p>
                    <div class="bg-gray-700 h-2 rounded-full overflow-hidden">
                        <div class="bg-red-500 h-full transition-all duration-500" 
                             style="width: {{ (($phaseProgress['warmup'] ?? 0) / 2) * 100 }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm mb-2">Momentum ({{ $phaseProgress['momentum'] ?? 0 }}/2)</p>
                    <div class="bg-gray-700 h-2 rounded-full overflow-hidden">
                        <div class="bg-yellow-500 h-full transition-all duration-500" 
                             style="width: {{ (($phaseProgress['momentum'] ?? 0) / 2) * 100 }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm mb-2">Deep Dive ({{ $phaseProgress['deepdive'] ?? 0 }}/2)</p>
                    <div class="bg-gray-700 h-2 rounded-full overflow-hidden">
                        <div class="bg-blue-500 h-full transition-all duration-500" 
                             style="width: {{ (($phaseProgress['deepdive'] ?? 0) / 2) * 100 }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm mb-2">Finale ({{ $phaseProgress['finale'] ?? 0 }}/1)</p>
                    <div class="bg-gray-700 h-2 rounded-full overflow-hidden">
                        <div class="bg-green-500 h-full transition-all duration-500" 
                             style="width: {{ (($phaseProgress['finale'] ?? 0) / 1) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        <!-- Challenges -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            @foreach($challenges as $challenge)
            <div class="bg-gray-800 rounded-lg p-6 {{ $challenge['passed'] ? 'ring-2 ring-green-500' : '' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-bold flex items-center">
                            {{ $challenge['name'] }}
                            @if($challenge['passed'])
                                <i class="fas fa-check-circle text-green-500 ml-2"></i>
                            @endif
                        </h3>
                        <p class="text-gray-400 mt-2">{{ $challenge['description'] }}</p>
                    </div>
                    <span class="bg-blue-600 text-sm px-3 py-1 rounded-full">
                        {{ $challenge['points'] }} pts
                    </span>
                </div>

                @if(!$challenge['passed'])
                <button onclick="openChallenge({{ $challenge['id'] }}, '{{ $challenge['name'] }}')" 
                        class="w-full bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-code mr-2"></i>Submit Solution
                </button>
                @else
                <div class="bg-green-900/30 p-3 rounded-lg">
                    <p class="text-green-400 text-sm">
                        <i class="fas fa-trophy mr-2"></i>Challenge completed!
                    </p>
                </div>
                @endif

                @if($challenge['last_submission'] && !$challenge['passed'])
                <div class="mt-4 bg-red-900/30 p-3 rounded-lg">
                    <p class="text-red-400 text-sm">
                        <i class="fas fa-times-circle mr-2"></i>Last attempt failed
                    </p>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Code Submission Modal -->
        <div id="submissionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold" id="modalTitle">Submit Solution</h2>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">Your Code:</label>
                    <textarea id="codeEditor" class="w-full h-96"></textarea>
                </div>

                <div class="flex justify-between items-center">
                    <div id="testResult" class="flex-1"></div>
                    <div class="flex space-x-3">
                        <button onclick="testCode()" 
                                id="submitButton"
                                class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded-lg transition">
                            <i class="fas fa-play mr-2"></i>Run Tests
                        </button>
                        <button onclick="closeModal()" class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded-lg transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let editor;
        let currentChallengeId = null;
        
        // Initialize CodeMirror
        document.addEventListener('DOMContentLoaded', function() {
            editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
                mode: 'python',
                theme: 'monokai',
                lineNumbers: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 4,
                extraKeys: {"Tab": "insertSoftTab"}
            });
        });

        // Set up axios defaults
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        function openChallenge(challengeId, challengeName) {
            currentChallengeId = challengeId;
            document.getElementById('modalTitle').textContent = challengeName;
            document.getElementById('submissionModal').classList.remove('hidden');
            document.getElementById('testResult').innerHTML = '';
            
            // Set starter code based on challenge
            const starterCode = getStarterCode(challengeName);
            editor.setValue(starterCode);
            editor.refresh();
        }

        function closeModal() {
            document.getElementById('submissionModal').classList.add('hidden');
            currentChallengeId = null;
        }

        function getStarterCode(challengeName) {
            const starters = {
                'Hello Hackathon': 'def main():\n    # Print "Hello Hackathon"\n    pass\n\nif __name__ == "__main__":\n    main()',
                'Sum Two Numbers': 'def add_numbers(a, b):\n    # Return the sum of a and b\n    pass',
                'Fibonacci Sequence': 'def fibonacci(n):\n    # Return first n fibonacci numbers as a list\n    pass',
                'Palindrome Checker': 'def is_palindrome(s):\n    # Return True if s is palindrome, False otherwise\n    # Handle spaces and case\n    pass',
                'Binary Tree Traversal': 'class TreeNode:\n    def __init__(self, val=0, left=None, right=None):\n        self.val = val\n        self.left = left\n        self.right = right\n\ndef inorder_traversal(root):\n    # Return inorder traversal as list\n    pass',
                'Dynamic Programming': 'def knapsack(weights, values, capacity):\n    # Return maximum value that can be obtained\n    pass',
                'Boss Challenge': '# Optimize the delivery route algorithm\n# Good luck!\n'
            };
            
            return starters[challengeName] || '# Write your solution here\n';
        }

        async function testCode() {
            if (!currentChallengeId) return;
            
            const code = editor.getValue();
            const button = document.getElementById('submitButton');
            const resultDiv = document.getElementById('testResult');
            
            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
            resultDiv.innerHTML = '<div class="text-yellow-400"><i class="fas fa-hourglass-half mr-2"></i>Running tests...</div>';
            
            try {
                const response = await axios.post('{{ route("team.submit") }}', {
                    challenge_id: currentChallengeId,
                    code: code
                });
                
                if (response.data.status === 'passed') {
                    resultDiv.innerHTML = `
                        <div class="bg-green-900/30 p-3 rounded-lg">
                            <p class="text-green-400 font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>${response.data.message}
                            </p>
                        </div>
                    `;
                    
                    // Reload page after 2 seconds to show updated progress
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else if (response.data.status === 'already_passed') {
                    resultDiv.innerHTML = `
                        <div class="bg-blue-900/30 p-3 rounded-lg">
                            <p class="text-blue-400">
                                <i class="fas fa-info-circle mr-2"></i>${response.data.message}
                            </p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-900/30 p-3 rounded-lg">
                            <p class="text-red-400 font-semibold mb-2">
                                <i class="fas fa-times-circle mr-2"></i>${response.data.message}
                            </p>
                            ${response.data.output ? `<pre class="text-xs text-gray-300 mt-2 overflow-x-auto">${response.data.output}</pre>` : ''}
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="bg-red-900/30 p-3 rounded-lg">
                        <p class="text-red-400">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Error: ${error.message}
                        </p>
                    </div>
                `;
            } finally {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-play mr-2"></i>Run Tests';
            }
        }

        // Auto-refresh every 30 seconds to update phase info
        setInterval(() => {
            window.location.reload();
        }, 30000);

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>