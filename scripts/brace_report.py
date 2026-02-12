from pathlib import Path
s=Path('Doctors/DoctorDashboard.js').read_text(encoding='utf-8')
lines=s.splitlines()
brace=0
for i,line in enumerate(lines,1):
    for ch in line:
        if ch=='{': brace+=1
        elif ch=='}': brace-=1
    if i<=220:
        print(f"{i:4} brace={brace:3} | {line}")
    else:
        break
