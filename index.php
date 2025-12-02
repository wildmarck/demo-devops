<?php
session_start();

// 1. Gestion de la Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: auth.php");
    exit();
}

// 2. Vérification de sécurité
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Météo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            /* Arrière-plan dynamique */
            background-image: url('image.gif');
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        
        /* Classes utilitaires Glassmorphism */
        .glass-panel {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 1rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Animation de chargement */
        .loader {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Transition douce pour les cartes météo */
        .weather-card {
            transition: transform 0.3s ease;
        }
        .weather-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.35);
        }
    </style>
</head>
<body class="text-white">

    <nav class="glass-nav fixed w-full z-50 top-0 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-cloud-sun text-2xl text-yellow-300"></i>
            <h1 class="text-xl font-bold tracking-wide">Météo<span class="font-light">App</span></h1>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white bg-opacity-30 flex items-center justify-center">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span class="font-semibold hidden sm:block"><?= $username ?></span>
            </div>
            <a href="?logout=true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition shadow-md text-sm font-bold">
                <i class="fa-solid fa-right-from-bracket mr-2"></i>Déconnexion
            </a>
        </div>
    </nav>

    <div class="pt-24 pb-10 px-4 container mx-auto max-w-4xl">
        
        <div class="glass-panel p-8 mb-8 text-center">
            <h2 class="text-3xl font-bold mb-6 drop-shadow-md">Quel temps fait-il aujourd'hui ?</h2>
            
            <form id="meteoForm" class="flex flex-col sm:flex-row gap-4 justify-center max-w-lg mx-auto">
                <div class="relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-4 text-white text-opacity-70"></i>
                    <input type="text" id="ville" placeholder="Entrez une ville (ex: Paris)" required
                           class="w-full pl-10 pr-4 py-3 rounded-xl bg-white bg-opacity-20 border border-white border-opacity-30 placeholder-white placeholder-opacity-70 focus:outline-none focus:bg-opacity-30 transition text-white">
                </div>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition transform hover:scale-105">
                    Rechercher
                </button>
            </form>
        </div>

        <div id="loader" class="hidden flex justify-center my-8">
            <div class="loader"></div>
        </div>

        <div id="resultat-container" class="hidden">
            <div id="resultat" class="glass-panel p-8 mb-8 flex flex-col items-center animate-fade-in-up">
                </div>

            <div id="previsions-section" class="hidden">
                <h3 class="text-2xl font-bold mb-6 pl-2 border-l-4 border-white">Prévisions sur 5 jours</h3>
                <div id="previsions" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                    </div>
            </div>
        </div>
        
    </div>

    <script>
        document.getElementById("meteoForm").addEventListener("submit", function(event) {
            event.preventDefault();
            
            const ville = document.getElementById("ville").value;
            const loader = document.getElementById("loader");
            const resultContainer = document.getElementById("resultat-container");
            const resultatDiv = document.getElementById("resultat");
            const previsionsDiv = document.getElementById("previsions");
            const previsionsSection = document.getElementById("previsions-section");

            // UI Reset
            loader.classList.remove("hidden");
            resultContainer.classList.add("hidden");
            resultatDiv.innerHTML = "";
            previsionsDiv.innerHTML = "";

            // Appel API Météo Actuelle
            fetch(`api.php?ville=${ville}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        loader.classList.add("hidden");
                        resultContainer.classList.remove("hidden");
                        resultatDiv.innerHTML = `<p class='text-red-300 font-bold text-xl'><i class="fa-solid fa-circle-exclamation mr-2"></i>${data.error}</p>`;
                        previsionsSection.classList.add("hidden");
                    } else {
                        // Affichage Météo Actuelle
                        const iconUrl = `https://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png`;
                        
                        resultatDiv.innerHTML = `
                            <h2 class="text-4xl font-bold mb-2">${data.name}, ${data.sys.country}</h2>
                            <p class="text-lg opacity-80 mb-4 capitalize">${data.weather[0].description}</p>
                            <div class="flex items-center justify-center gap-4">
                                <img src="${iconUrl}" alt="Météo" class="w-32 h-32 drop-shadow-lg filter drop-shadow-lg">
                                <div class="text-6xl font-bold">${Math.round(data.main.temp)}°C</div>
                            </div>
                            <div class="flex gap-8 mt-6 text-sm sm:text-base bg-white bg-opacity-10 p-4 rounded-xl">
                                <div><i class="fa-solid fa-droplet mr-2"></i>Humidité: <strong>${data.main.humidity}%</strong></div>
                                <div><i class="fa-solid fa-wind mr-2"></i>Vent: <strong>${data.wind.speed} km/h</strong></div>
                            </div>
                        `;

                        // Appel API Prévisions
                        fetch(`api.php?ville=${ville}&previsions=1`)
                            .then(response => response.json())
                            .then(forecastData => {
                                loader.classList.add("hidden");
                                resultContainer.classList.remove("hidden");
                                previsionsSection.classList.remove("hidden");
                                
                                let forecastHTML = '';
                                forecastData.list.forEach((item, index) => {
                                    // On prend une prévision toutes les 24h (index % 8 car les données sont toutes les 3h)
                                    if (index % 8 === 0) {
                                        const date = new Date(item.dt_txt).toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric' });
                                        forecastHTML += `
                                            <div class="glass-panel weather-card p-4 flex flex-col items-center justify-center">
                                                <p class="font-bold text-lg mb-1 capitalize">${date}</p>
                                                <img src="https://openweathermap.org/img/wn/${item.weather[0].icon}.png" alt="Icone" class="w-16 h-16">
                                                <p class="text-xl font-bold mt-1">${Math.round(item.main.temp)}°C</p>
                                                <p class="text-xs opacity-70 capitalize">${item.weather[0].description}</p>
                                            </div>`;
                                    }
                                });
                                previsionsDiv.innerHTML = forecastHTML;
                            });
                    }
                })
                .catch(error => {
                    console.error("Erreur :", error);
                    loader.classList.add("hidden");
                });
        });
    </script>
</body>
</html>