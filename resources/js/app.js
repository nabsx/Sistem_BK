import "./bootstrap";

document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector("[data-menu-toggle]");
    const sidebar = document.querySelector("#sidebar");
    const passwordToggle = document.querySelector("[data-password-toggle]");
    const password = document.querySelector("#password");

    toggle?.addEventListener("click", () => sidebar?.classList.toggle("open"));
    document
        .querySelectorAll(".nav-item")
        .forEach((link) =>
            link.addEventListener("click", () =>
                sidebar?.classList.remove("open"),
            ),
        );

    const profileMenu = document.querySelector("[data-profile-menu]");
    const profileToggle = document.querySelector("[data-profile-toggle]");
    const profileDropdown = document.querySelector("[data-profile-dropdown]");
    const closeProfileMenu = () => {
        if (!profileDropdown || !profileToggle) return;
        profileDropdown.hidden = true;
        profileToggle.setAttribute("aria-expanded", "false");
        profileMenu?.classList.remove("is-open");
    };
    profileToggle?.addEventListener("click", () => {
        if (!profileDropdown) return;
        const isOpen = !profileDropdown.hidden;
        profileDropdown.hidden = isOpen;
        profileToggle.setAttribute("aria-expanded", String(!isOpen));
        profileMenu?.classList.toggle("is-open", !isOpen);
    });
    document.addEventListener("click", (event) => {
        if (profileMenu && !profileMenu.contains(event.target))
            closeProfileMenu();
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeProfileMenu();
    });

    passwordToggle?.addEventListener("click", () => {
        if (!password) return;
        const visible = password.type === "text";
        password.type = visible ? "password" : "text";
        passwordToggle.setAttribute(
            "aria-label",
            visible ? "Tampilkan kata sandi" : "Sembunyikan kata sandi",
        );
    });

    const studentLookup = document.querySelector("[data-student-lookup]");
    if (studentLookup) {
        const query = studentLookup.querySelector("[data-student-query]");
        const results = studentLookup.querySelector("[data-student-results]");
        const hiddenId = studentLookup.querySelector("[data-student-id]");
        const selected = document.querySelector("[data-selected-student]");
        let timer;
        const renderResults = (students) => {
            results.innerHTML = students.length
                ? students
                      .map(
                          (student) =>
                              `<button type="button" class="student-result" data-student-id="${student.id}" data-student-name="${student.name}" data-student-meta="${student.nis} · ${student.class ?? "Belum ada kelas"}" data-student-points="${student.points ?? 0}"><strong>${student.name}</strong><small>${student.nis} · ${student.class ?? "Belum ada kelas"}</small></button>`,
                      )
                      .join("")
                : '<div class="student-result-empty">Siswa tidak ditemukan</div>';
            results.hidden = false;
        };
        query?.addEventListener("input", () => {
            clearTimeout(timer);
            hiddenId.value = "";
            selected.hidden = true;
            const term = query.value.trim();
            if (term.length < 2) {
                results.hidden = true;
                return;
            }
            timer = setTimeout(async () => {
                const response = await fetch(
                    `${studentLookup.dataset.endpoint}?q=${encodeURIComponent(term)}`,
                    { headers: { Accept: "application/json" } },
                );
                renderResults(await response.json());
            }, 250);
        });
        results?.addEventListener("click", (event) => {
            const button = event.target.closest("[data-student-id]");
            if (!button) return;
            hiddenId.value = button.dataset.studentId;
            query.value = button.dataset.studentName;
            results.hidden = true;
            selected.hidden = false;
            selected.querySelector("[data-selected-initials]").textContent =
                button.dataset.studentName
                    .split(" ")
                    .slice(0, 2)
                    .map((word) => word[0])
                    .join("")
                    .toUpperCase();
            selected.querySelector("[data-selected-name]").textContent =
                button.dataset.studentName;
            selected.querySelector("[data-selected-meta]").textContent =
                button.dataset.studentMeta;
            selected.querySelector("[data-selected-points]").textContent =
                button.dataset.studentPoints;
        });
        studentLookup
            .querySelector("[data-student-clear]")
            ?.addEventListener("click", () => {
                hiddenId.value = "";
                query.value = "";
                selected.hidden = true;
                query.focus();
            });
        document.addEventListener("click", (event) => {
            if (!studentLookup.contains(event.target)) results.hidden = true;
        });
    }

    const directory = document.querySelector("[data-student-directory]");
    if (directory) {
        const search = directory.querySelector("[data-student-search]");
        const rows = [...directory.querySelectorAll("[data-student-row]")];
        const filters = [
            ...directory.querySelectorAll("[data-student-filter]"),
        ];
        const applyFilters = () => {
            const term = search?.value.trim().toLowerCase() ?? "";
            rows.forEach((row) => {
                const matchesTerm =
                    !term ||
                    `${row.dataset.name} ${row.dataset.nis}`.includes(term);
                const matchesFilters = filters.every(
                    (filter) =>
                        !filter.value ||
                        row.dataset[filter.dataset.studentFilter] ===
                            filter.value.toLowerCase(),
                );
                row.hidden = !(matchesTerm && matchesFilters);
            });
        };
        search?.addEventListener("input", applyFilters);
        filters.forEach((filter) =>
            filter.addEventListener("change", applyFilters),
        );
        directory
            .querySelector("[data-sort-points]")
            ?.addEventListener("click", () => {
                const table = directory.querySelector("[data-student-table]");
                rows.sort(
                    (a, b) =>
                        Number(b.dataset.points) - Number(a.dataset.points),
                ).forEach((row) => table?.appendChild(row));
            });
        directory.querySelectorAll("[data-view]").forEach((button) =>
            button.addEventListener("click", () => {
                directory
                    .querySelectorAll("[data-view]")
                    .forEach((item) =>
                        item.classList.toggle("is-active", item === button),
                    );
                directory.classList.toggle(
                    "is-grid-view",
                    button.dataset.view === "grid",
                );
            }),
        );
        document.addEventListener("keydown", (event) => {
            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === "k"
            ) {
                event.preventDefault();
                search?.focus();
            }
        });
    }
});
