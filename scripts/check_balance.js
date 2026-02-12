const fs = require("fs");
function check(file) {
  const s = fs.readFileSync(file, "utf8");
  const pairs = { "(": ")", "[": "]", "{": "}" };
  const stack = [];
  for (let i = 0; i < s.length; i++) {
    const ch = s[i];
    if ("([{".includes(ch)) stack.push({ c: ch, pos: i });
    else if (")]}".includes(ch)) {
      if (!stack.length) {
        console.log(file, "Unmatched closing", ch, "at", i);
        return;
      }
      const o = stack.pop();
      if (pairs[o.c] !== ch) {
        console.log(
          file,
          "Mismatched",
          o.c,
          "at",
          o.pos,
          "closed by",
          ch,
          "at",
          i
        );
        return;
      }
    }
  }
  if (stack.length)
    console.log(file, "Unclosed at EOF", stack[stack.length - 1]);
  else console.log(file, "All balanced");
}
check("Doctors/DoctorDashboard.js");
check("Coaches/CoachDashboard.js");
check("Doctors/MealPlans.js");
