const fs = require("fs");
const vm = require("vm");
const srcPath = __dirname + "/../Store/Store/data.js";
const dstPath = __dirname + "/../Store/Store/store_products_full.json";
if (!fs.existsSync(srcPath)) {
  console.error("data.js not found");
  process.exit(1);
}
const src = fs.readFileSync(srcPath, "utf8");
try {
  const script = new vm.Script(src + "\nstoreProducts;");
  const context = {};
  const result = script.runInNewContext(context, { timeout: 2000 });
  if (!Array.isArray(result)) {
    console.error("Parsed content is not an array");
    process.exit(1);
  }
  fs.writeFileSync(dstPath, JSON.stringify(result, null, 2), "utf8");
  console.log(`Wrote ${result.length} products to ${dstPath}`);
} catch (e) {
  console.error("Failed to parse data.js:", e.message);
  process.exit(1);
}
