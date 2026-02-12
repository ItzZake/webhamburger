document.addEventListener("DOMContentLoaded", function () {
  const tbody = document.querySelector("#products tbody");
  const form = document.getElementById("createForm");

  function load() {
    fetch("/api/admin/products/list.php")
      .then((r) => r.json())
      .then((rows) => {
        tbody.innerHTML = "";
        rows.forEach((rw) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `<td>${rw.id}</td><td>${rw.name}</td><td>${rw.price}</td><td>${rw.is_active}</td><td><button data-id="${rw.id}" class="del">Delete</button> <button data-id="${rw.id}" class="edit">Edit</button></td>`;
          tbody.appendChild(tr);
        });
      });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    fetch("/api/admin/products/create.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    })
      .then((r) => r.json())
      .then((resp) => {
        handleResp(resp);
        form.reset();
        load();
      });
  });
  // toast feedback
  function handleResp(resp) {
    if (resp && resp.inserted) showToast("Product created", "success");
    else if (resp && resp.error) showToast(resp.error, "error");
  }

  tbody.addEventListener("click", function (e) {
    if (e.target.matches(".del")) {
      const id = e.target.dataset.id;
      if (!confirm("delete?")) return;
      fetch("/api/admin/products/delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
      }).then(() => load());
    }
    if (e.target.matches(".edit")) {
      const id = e.target.dataset.id;
      const name = prompt("new name");
      if (name == null) return;
      fetch("/api/admin/products/update.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, name }),
      }).then(() => load());
    }
  });

  load();
});
