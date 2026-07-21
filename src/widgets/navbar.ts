function switchTheme() {
  const currentTheme = localStorage.getItem("color-scheme-fuwari") || "auto";
  let newTheme: string;

  // Cycle through: auto -> dark -> light -> auto
  if (currentTheme === "auto") {
    newTheme = "dark";
  } else if (currentTheme === "dark") {
    newTheme = "light";
  } else {
    newTheme = "auto";
  }

  // Apply the new theme
  if (newTheme === "dark") {
    document.documentElement.classList.add("dark");
    document.documentElement.classList.remove("light");
  } else if (newTheme === "light") {
    document.documentElement.classList.remove("dark");
    document.documentElement.classList.add("light");
  } else {
    // auto mode
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    if (prefersDark) {
      document.documentElement.classList.add("dark");
      document.documentElement.classList.remove("light");
    } else {
      document.documentElement.classList.remove("dark");
      document.documentElement.classList.add("light");
    }
  }

  // Save to localStorage
  localStorage.setItem("color-scheme-fuwari", newTheme);

  // Update button icon if needed
  updateThemeIcon(newTheme);
}

function updateThemeIcon(theme: string) {
  const switchBtn = document.getElementById("scheme-switch");
  if (!switchBtn) return;

  const iconSpan = switchBtn.querySelector("span");
  if (!iconSpan) return;

  // Remove all possible icon classes
  iconSpan.classList.remove(
    "icon-[material-symbols--dark-mode-outline]",
    "icon-[material-symbols--sunny-outline-rounded]",
    "icon-[material-symbols--radio-button-partial-outline]"
  );

  // Add the appropriate icon class
  if (theme === "dark") {
    iconSpan.classList.add("icon-[material-symbols--moon-stars-outline-rounded]");
  } else if (theme === "light") {
    iconSpan.classList.add("icon-[material-symbols--sunny-outline-rounded]");
  } else {
    iconSpan.classList.add("icon-[material-symbols--radio-button-partial-outline]");
  }
}

function loadButtonScript() {
  const switchBtn = document.getElementById("scheme-switch");
  if (switchBtn) {
    switchBtn.onclick = function () {
      switchTheme();
    };
  }

  const settingBtn = document.getElementById("display-settings-switch");
  if (settingBtn) {
    settingBtn.onclick = function () {
      const settingPanel = document.getElementById("display-setting");
      if (settingPanel) {
        settingPanel.classList.toggle("float-panel-closed");
      }
    };
  }

  const menuBtn = document.getElementById("nav-menu-switch");
  if (menuBtn) {
    menuBtn.onclick = function () {
      const menuPanel = document.getElementById("nav-menu-panel");
      if (menuPanel) {
        menuPanel.classList.toggle("float-panel-closed");
      }
    };
  }

  // Initialize icon based on current theme
  const currentTheme = localStorage.getItem("color-scheme-fuwari") || "auto";
  updateThemeIcon(currentTheme);
}

export { loadButtonScript };
