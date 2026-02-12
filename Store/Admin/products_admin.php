<?php
require_once __DIR__ . '/../../api/helpers/auth.php';
require_role('admin');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products Admin</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px} table{width:100%;border-collapse:collapse} td,th{border:1px solid #ddd;padding:8px}</style>
  <script defer src="/assets/js/toast.js"></script>
  <script defer src="products_admin.js"></script>
</head>
<body>
  <h1>Products Admin</h1>
  <div>
    <h3>Create product</h3>
    <form id="createForm"><input name="name" placeholder="Name" required /><input name="price" placeholder="Price" required /><input name="img" placeholder="Image URL" /><button>Create</button></form>
  </div>
  <table id="products"><thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Active</th><th>Actions</th></tr></thead><tbody></tbody></table>
</body>
</html>
