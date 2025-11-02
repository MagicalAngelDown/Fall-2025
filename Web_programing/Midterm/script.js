(function () {
  const form = document.getElementById("userForm");
  const result = document.getElementById("result");
  const year = document.getElementById("year");
  if (year) year.textContent = new Date().getFullYear();

  const byId = (id) => document.getElementById(id);
  const setError = (id, msg) => { const el = byId(id); if (el) el.textContent = msg || ""; };
  const clearAllErrors = () => {
    ["first_name_error","last_name_error","email_error","phone_error","website_error","department_error","title_error"]
      .forEach(id => setError(id, ""));
  };

  // Validation rules
  const emailValid = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
    return re.test(String(email || ""));
  };

  const phoneValid = (value) => {
    if (!value) return true; // optional
    const digits = (value.match(/\d/g) || []).join("");
    return digits.length >= 10 && digits.length <= 15;
  };

  const urlValid = (value) => {
    if (!value) return true; // optional
    try {
      const u = new URL(value);
      return u.protocol === "http:" || u.protocol === "https:";
    } catch {
      return false;
    }
  };

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    clearAllErrors();

    const data = {
      first: byId("first_name").value.trim(),
      last: byId("last_name").value.trim(),
      email: byId("email_address").value.trim(),
      department: byId("department").value,
      title: byId("title").value,
      website: byId("website").value.trim(),
      phone: byId("phone_number").value.trim(),
      isDr: byId("is_dr").checked
    };

    let ok = true;

    // Required checks
    if (!data.first) { setError("first_name_error", "First name is required."); ok = false; }
    if (!data.last) { setError("last_name_error", "Last name is required."); ok = false; }
    if (!data.email) { setError("email_error", "Email address is required."); ok = false; }
    if (!data.department) { setError("department_error", "Please choose a department."); ok = false; }
    if (!data.title) { setError("title_error", "Please choose a title."); ok = false; }

    // Format checks
    if (data.email && !emailValid(data.email)) { setError("email_error", "Please enter a valid email address."); ok = false; }
    if (!phoneValid(data.phone)) { setError("phone_error", "Enter 10–15 digits (formats like 555-555-5555 or (555) 555-5555 allowed)."); ok = false; }
    if (!urlValid(data.website)) { setError("website_error", "Please enter a valid URL starting with http(s)://"); ok = false; }

    if (!ok) {
      result.innerHTML = '<p class="warn">Please fix the errors above and resubmit.</p>';
      return;
    }

    // Build display
    const fullName = (data.isDr ? "Dr. " : "") + data.first + " " + data.last;
    const emailLink = `<a href="mailto:${encodeURIComponent(data.email)}">${escapeHtml(data.email)}</a>`;
    const phoneOut = data.phone ? formatPhone(data.phone) : "";
    const websiteLink = data.website ? `<a href="${escapeAttr(data.website)}" target="_blank" rel="noopener noreferrer">${escapeHtml(data.website)}</a>` : "";

    const parts = [
      `<div class="row"><span class="label">Full Name:</span><span>${escapeHtml(fullName)}</span></div>`,
      `<div class="row"><span class="label">Department:</span><span>${escapeHtml(data.department)}</span></div>`,
      `<div class="row"><span class="label">Title:</span><span>${escapeHtml(data.title)}</span></div>`,
      `<div class="row"><span class="label">Email:</span><span>${emailLink}</span></div>`
    ];

    if (phoneOut) parts.push(`<div class="row"><span class="label">Phone:</span><span>${escapeHtml(phoneOut)}</span></div>`);
    if (websiteLink) parts.push(`<div class="row"><span class="label">Website:</span><span>${websiteLink}</span></div>`);

    result.innerHTML = `<div class="card success"><h3>Submission Received</h3>${parts.join("")}</div>`;
    result.scrollIntoView({ behavior: "smooth", block: "start" });
  });

  form.addEventListener("reset", () => {
    clearAllErrors();
    result.innerHTML = "<p>Form cleared. Fill the form and press Submit.</p>";
  });

  // Utilities
  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;"
    })[s]);
  }
  function escapeAttr(str) {
    // keep as-is but ensure it can be placed inside quotes
    return String(str).replace(/"/g, "&quot;");
  }
  function formatPhone(value) {
    const digits = (value.match(/\d/g) || []).join("");
    if (digits.length === 10) {
      return `(${digits.slice(0,3)}) ${digits.slice(3,6)}-${digits.slice(6)}`;
    }
    return value;
  }
})();