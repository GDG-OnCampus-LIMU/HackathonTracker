#!/bin/bash
# Hackathon Manager - Complete Setup and Management Script

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Check prerequisites
check_requirements() {
    print_header "Checking Requirements"
    
    # Check PHP
    if command -v php &> /dev/null; then
        print_success "PHP installed: $(php -v | head -n 1)"
    else
        print_error "PHP not installed"
        exit 1
    fi
    
    # Check Composer
    if command -v composer &> /dev/null; then
        print_success "Composer installed"
    else
        print_error "Composer not installed"
        exit 1
    fi
    
    # Check Python
    if command -v python3 &> /dev/null; then
        print_success "Python3 installed: $(python3 --version)"
    else
        print_error "Python3 not installed"
        exit 1
    fi
    
    # Check if in Laravel directory
    if [ -f "artisan" ]; then
        print_success "In Laravel project directory"
    else
        print_error "Not in Laravel project directory"
        exit 1
    fi
}

# Initial setup
initial_setup() {
    print_header "Running Initial Setup"
    
    # Create .env if doesn't exist
    if [ ! -f .env ]; then
        cp .env.example .env
        php artisan key:generate
        print_success ".env file created"
    else
        print_info ".env file already exists"
    fi
    
    # Create directories
    mkdir -p storage/app/tests
    mkdir -p storage/app/submissions
    chmod -R 777 storage
    chmod -R 777 bootstrap/cache
    print_success "Directories created"
    
    # Run migrations
    print_info "Running migrations..."
    php artisan migrate --force
    print_success "Database migrated"
}

# Create all test scripts
create_test_scripts() {
    print_header "Creating Test Scripts"
    
    # Test 1: Hello Hackathon
    cat > storage/app/tests/test_hello.py << 'EOF'
#!/usr/bin/env python3
import sys
import subprocess

result = subprocess.run([sys.executable, sys.argv[1]], capture_output=True, text=True)
if result.stdout.strip() == "Hello Hackathon":
    print("All tests passed!")
    sys.exit(0)
else:
    print(f"Expected: Hello Hackathon")
    print(f"Got: {result.stdout.strip()}")
    sys.exit(1)
EOF

    # Test 2: Sum
    cat > storage/app/tests/test_sum.py << 'EOF'
#!/usr/bin/env python3
import sys
import importlib.util

spec = importlib.util.spec_from_file_location("submission", sys.argv[1])
submission = importlib.util.module_from_spec(spec)
spec.loader.exec_module(submission)

test_cases = [(2, 3, 5), (10, 15, 25), (-5, 5, 0), (100, 200, 300)]
for a, b, expected in test_cases:
    result = submission.add_numbers(a, b)
    if result != expected:
        print(f"Failed: add_numbers({a}, {b}) = {result}, expected {expected}")
        sys.exit(1)
print("All tests passed!")
sys.exit(0)
EOF

    # Test 3: Fibonacci
    cat > storage/app/tests/test_fibonacci.py << 'EOF'
#!/usr/bin/env python3
import sys
import importlib.util

spec = importlib.util.spec_from_file_location("submission", sys.argv[1])
submission = importlib.util.module_from_spec(spec)
spec.loader.exec_module(submission)

test_cases = [
    (1, [0]),
    (2, [0, 1]),
    (5, [0, 1, 1, 2, 3]),
    (10, [0, 1, 1, 2, 3, 5, 8, 13, 21, 34])
]
for n, expected in test_cases:
    result = submission.fibonacci(n)
    if result != expected:
        print(f"Failed: fibonacci({n}) = {result}, expected {expected}")
        sys.exit(1)
print("All tests passed!")
sys.exit(0)
EOF

    # Test 4: Palindrome
    cat > storage/app/tests/test_palindrome.py << 'EOF'
#!/usr/bin/env python3
import sys
import importlib.util

spec = importlib.util.spec_from_file_location("submission", sys.argv[1])
submission = importlib.util.module_from_spec(spec)
spec.loader.exec_module(submission)

test_cases = [
    ("racecar", True),
    ("hello", False),
    ("A man a plan a canal Panama", True),
    ("", True),
    ("ab", False)
]
for string, expected in test_cases:
    result = submission.is_palindrome(string)
    if result != expected:
        print(f"Failed: is_palindrome('{string}') = {result}, expected {expected}")
        sys.exit(1)
print("All tests passed!")
sys.exit(0)
EOF

    # Test 5: Binary Tree (placeholder)
    cat > storage/app/tests/test_tree.py << 'EOF'
#!/usr/bin/env python3
import sys
print("Binary tree test placeholder")
print("All tests passed!")
sys.exit(0)
EOF

    # Test 6: Knapsack (placeholder)
    cat > storage/app/tests/test_knapsack.py << 'EOF'
#!/usr/bin/env python3
import sys
print("Knapsack test placeholder")
print("All tests passed!")
sys.exit(0)
EOF

    # Test 7: Boss (placeholder)
    cat > storage/app/tests/test_boss.py << 'EOF'
#!/usr/bin/env python3
import sys
print("Boss challenge test placeholder")
print("All tests passed!")
sys.exit(0)
EOF

    chmod +x storage/app/tests/*.py
    print_success "Test scripts created"
}

# Show team tokens
show_tokens() {
    print_header "Team Access Tokens"
    php artisan tinker --execute="
    \App\Models\Team::all(['name', 'access_token'])->each(function(\$team) {
        echo str_pad(\$team->name . ':', 15) . \$team->access_token . PHP_EOL;
    });"
}

# Start hackathon
start_hackathon() {
    print_header "Starting Hackathon"
    
    # Clear previous submissions
    php artisan tinker --execute="\App\Models\Submission::truncate();" &> /dev/null
    php artisan tinker --execute="\App\Models\Team::query()->update(['is_finalist' => false]);" &> /dev/null
    print_success "Previous submissions cleared"
    
    # Show URLs
    echo ""
    print_info "Access URLs:"
    echo "  Team Portal:    http://localhost:8000/team/login"
    echo "  Admin Dashboard: http://localhost:8000/admin/dashboard"
    echo "  API Endpoint:    http://localhost:8000/api/progress"
    echo ""
    
    # Start server
    print_success "Starting Laravel server..."
    php artisan serve --host=0.0.0.0
}

# Monitor progress
monitor_progress() {
    print_header "Live Progress Monitor"
    watch -n 3 'curl -s http://localhost:8000/api/progress | jq -r ".teams[] | \"\(.name): Score=\((.phases.warmup * 10 + .phases.momentum * 20 + .phases.deepdive * 30)) W=\(.phases.warmup) M=\(.phases.momentum) D=\(.phases.deepdive) F=\(.phases.finale)\""'
}

# Reset for new hackathon
reset_hackathon() {
    print_header "Resetting Hackathon"
    
    read -p "Are you sure you want to reset all submissions? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan tinker --execute="\App\Models\Submission::truncate();" &> /dev/null
        php artisan tinker --execute="\App\Models\Team::query()->update(['is_finalist' => false]);" &> /dev/null
        print_success "All submissions cleared"
        print_success "Teams reset"
    else
        print_info "Reset cancelled"
    fi
}

# Generate report
generate_report() {
    print_header "Hackathon Final Report"
    
    # Get timestamp
    TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")
    FILENAME="hackathon_report_$(date +%Y%m%d_%H%M%S).txt"
    
    # Generate report
    {
        echo "HACKATHON FINAL REPORT"
        echo "Generated: $TIMESTAMP"
        echo "======================================"
        echo ""
        echo "FINAL STANDINGS:"
        php artisan tinker --execute="
        \$teams = \App\Models\Team::all()->map(function(\$team) {
            return [
                'name' => \$team->name,
                'score' => \$team->total_score,
                'finalist' => \$team->is_finalist,
                'warmup' => \App\Models\Submission::join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
                    ->where('submissions.team_id', \$team->id)
                    ->where('submissions.status', 'passed')
                    ->where('challenges.phase', 'warmup')
                    ->count(),
                'momentum' => \App\Models\Submission::join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
                    ->where('submissions.team_id', \$team->id)
                    ->where('submissions.status', 'passed')
                    ->where('challenges.phase', 'momentum')
                    ->count(),
                'deepdive' => \App\Models\Submission::join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
                    ->where('submissions.team_id', \$team->id)
                    ->where('submissions.status', 'passed')
                    ->where('challenges.phase', 'deepdive')
                    ->count(),
            ];
        })->sortByDesc('score');
        
        \$rank = 1;
        foreach(\$teams as \$team) {
            echo \$rank . '. ' . \$team['name'] . ': ';
            echo 'Score=' . \$team['score'] . ' ';
            echo '(W:' . \$team['warmup'] . '/2 ';
            echo 'M:' . \$team['momentum'] . '/2 ';
            echo 'D:' . \$team['deepdive'] . '/2) ';
            echo \$team['finalist'] ? '[FINALIST]' : '';
            echo PHP_EOL;
            \$rank++;
        }" 2>/dev/null
        
        echo ""
        echo "======================================"
        echo "SUBMISSION TIMELINE:"
        php artisan tinker --execute="
        \$submissions = \App\Models\Submission::with(['team', 'challenge'])
            ->where('status', 'passed')
            ->orderBy('created_at')
            ->get();
        
        foreach(\$submissions as \$sub) {
            echo \$sub->created_at->format('H:i:s') . ' - ';
            echo \$sub->team->name . ' completed ';
            echo \$sub->challenge->name . ' ';
            echo '(+' . \$sub->challenge->points . ' pts)';
            echo PHP_EOL;
        }" 2>/dev/null
    } > "$FILENAME"
    
    print_success "Report saved to: $FILENAME"
    
    # Also display on screen
    cat "$FILENAME"
}

# Quick status check
quick_status() {
    print_header "Quick Status Check"
    
    # Check if server is running
    if curl -s http://localhost:8000/api/progress &> /dev/null; then
        print_success "Server is running"
        
        # Get team count and total submissions
        TEAM_COUNT=$(curl -s http://localhost:8000/api/progress | jq '.teams | length')
        echo "  Teams tracked: $TEAM_COUNT"
        
        # Show current leaders
        echo ""
        echo "Current Leaders:"
        curl -s http://localhost:8000/api/progress | jq -r '.teams[] | 
            "\(.name): \((.phases.warmup * 10 + .phases.momentum * 20 + .phases.deepdive * 30)) points"' | 
            sort -t: -k2 -nr | head -3
    else
        print_error "Server not running"
        print_info "Start with: ./hackathon.sh start"
    fi
}

# Main menu
show_menu() {
    echo ""
    print_header "🚀 Hackathon Manager"
    echo "1) Initial Setup (first time only)"
    echo "2) Create Test Scripts"
    echo "3) Show Team Tokens"
    echo "4) Start Hackathon"
    echo "5) Monitor Progress"
    echo "6) Quick Status"
    echo "7) Generate Report"
    echo "8) Reset Hackathon"
    echo "9) Exit"
    echo ""
}

# Main script
case "$1" in
    setup)
        check_requirements
        initial_setup
        create_test_scripts
        show_tokens
        ;;
    start)
        start_hackathon
        ;;
    monitor)
        monitor_progress
        ;;
    tokens)
        show_tokens
        ;;
    status)
        quick_status
        ;;
    report)
        generate_report
        ;;
    reset)
        reset_hackathon
        ;;
    *)
        # Interactive menu
        while true; do
            show_menu
            read -p "Select option: " choice
            case $choice in
                1) check_requirements && initial_setup && create_test_scripts ;;
                2) create_test_scripts ;;
                3) show_tokens ;;
                4) start_hackathon ;;
                5) monitor_progress ;;
                6) quick_status ;;
                7) generate_report ;;
                8) reset_hackathon ;;
                9) echo "Goodbye!"; exit 0 ;;
                *) print_error "Invalid option" ;;
            esac
            
            if [[ $choice == 4 ]]; then
                break  # Exit after starting server
            fi
            
            echo ""
            read -p "Press Enter to continue..."
        done
        ;;
esac