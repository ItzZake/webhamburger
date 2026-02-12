from pathlib import Path
lines=Path('Doctors/DoctorDashboard.js').read_text(encoding='utf-8').splitlines()
for i in range(155,167):
    print(i+1, repr(lines[i]))
