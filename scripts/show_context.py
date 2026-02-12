from pathlib import Path
s=Path('Doctors/DoctorDashboard.js').read_text(encoding='utf-8')
pos=5380
lines=s.splitlines()
char_count=0
for i,line in enumerate(lines,1):
    char_count += len(line)+1
    if char_count>=pos:
        start_line=max(1,i-5)
        end_line=min(len(lines),i+5)
        print('position',pos,'approx at line',i)
        for ln in range(start_line,end_line+1):
            print(f"{ln:4}: {lines[ln-1]}")
        break
