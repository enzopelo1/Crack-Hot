function openNav() {
    document.getElementById("mySidenav").style.width = "30%";
    document.getElementById("settingsIcon").classList.add("hidden");
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("settingsIcon").classList.remove("hidden");
}

function toggleMode() {
    var element = document.body;
    element.classList.toggle("white-mode");
    var modeToggle = document.getElementById("modeToggle");
    if (element.classList.contains("white-mode")) {
        localStorage.setItem("mode", "white-mode");
        modeToggle.checked = true;
    } else {
        localStorage.removeItem("mode");
        modeToggle.checked = false;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var modeToggle = document.getElementById("modeToggle");
    if (localStorage.getItem("mode") === "white-mode") {
        document.body.classList.add("white-mode");
        modeToggle.checked = true;
    } else {
        modeToggle.checked = false;
    }
});

let currentLang = "fr"; // Langue par défaut

// Charger les traductions depuis le JSON
async function loadTranslations(lang) {
    try {
        const response = await fetch("../public/lang/translations.json");
        const translations = await response.json();

        applyTranslations(translations[lang]);
        currentLang = lang; // Met à jour la langue actuelle
    } catch (error) {
        console.error("Erreur lors du chargement des traductions :", error);
    }
}

// Appliquer les traductions aux éléments de la page
function applyTranslations(translations) {
    document.querySelectorAll("[data-translate]").forEach(element => {
        const key = element.getAttribute("data-translate");
        if (translations[key]) {
            element.textContent = translations[key];
        }
    });
}

// Ajouter des écouteurs d'événements pour le changement de langue
document.querySelector(".langue").addEventListener("click", event => {
    if (event.target.tagName === "A") {
        const lang = event.target.textContent.trim().toLowerCase();
        if (lang === "français" || lang === "english") {
            loadTranslations(lang === "français" ? "fr" : "en");
        }
    }
});

// Charger les traductions initiales
loadTranslations(currentLang);
