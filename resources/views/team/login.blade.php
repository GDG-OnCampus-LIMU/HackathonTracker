<!-- resources/views/team/login.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Team Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-code text-6xl text-blue-500 mb-4"></i>
            <h1 class="text-3xl font-bold text-white">Hackathon Portal</h1>
            <p class="text-gray-400 mt-2">Enter your team token to continue</p>
        </div>

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('team.login.submit') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-300 text-sm font-semibold mb-2">
                    Team Token
                </label>
                <div class="relative">
                    <input type="password" 
                           name="token" 
                           class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           placeholder="Enter your team token"
                           required>
                    <i class="fas fa-key absolute right-3 top-3.5 text-gray-400"></i>
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-200 transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Access Team Portal
            </button>
        </form>

        <div class="mt-8 text-center text-gray-400 text-sm">
            <p>Don't have a token? Contact your hackathon organizer.</p>
        </div>

        <!-- Fun animation -->
        <style>
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }
            .fa-code {
                animation: float 3s ease-in-out infinite;
            }
        </style>
    </div>
</body>
</html>