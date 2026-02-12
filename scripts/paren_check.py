from pathlib import Path
s=Path('Doctors/DoctorDashboard.js').read_text(encoding='utf-8')
paren=0
brack=0
brace=0
for i,ch in enumerate(s,1):
    if ch=='(':
        paren+=1
    elif ch==')':
        paren-=1
    elif ch=='[':
        brack+=1
    elif ch==']':
        brack-=1
    elif ch=='{':
        brace+=1
    elif ch=='}':
        brace-=1
    if i%200==0:
        # show progress
        pass
    if paren<0 or brack<0 or brace<0:
        print('Negative at index',i,'char',ch)
        break

# print counters at approx position 164 lines
lines=s.splitlines()
line_no=164
charpos=sum(len(lines[i])+1 for i in range(line_no-1))
print('counts at line',line_no, 'paren,brack,brace=',paren,brack,brace,'charpos',charpos)
print('line',line_no,':',lines[line_no-1])
