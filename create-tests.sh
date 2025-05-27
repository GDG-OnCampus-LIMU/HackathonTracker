#!/bin/bash
echo "Creating test scripts..."

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

# Test 2: Sum Numbers
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

# Add more test scripts here...

chmod +x storage/app/tests/*.py
echo "✅ Test scripts created!"