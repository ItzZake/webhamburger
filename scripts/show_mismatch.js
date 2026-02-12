const fs = require("fs");
function show(file, pos, len = 200) {
  const s = fs.readFileSync(file, "utf8");
  const start = Math.max(0, pos - 100);
  const ctx = s.slice(start, pos + len);
  const before = s.slice(0, pos).split("\n").length;
  console.log(file, "pos", pos, "approx line", before);
  console.log("--- context ---");
  console.log(ctx);
}
show("Doctors/DoctorDashboard.js", 5380, 400);
show("Doctors/DoctorDashboard.js", 5663, 400);
show("Coaches/CoachDashboard.js", 2639, 400);
show("Coaches/CoachDashboard.js", 9137, 400);
