<?php

session_start();

// Connexion DB
$host = 'localhost';
$dbname = 'ang_decision';
$user = 'root';
$password = '';

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Récupération du pseudo
$pseudo = isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : null;

if ($pseudo) {
    $requete = $bdd->prepare('SELECT fullname, username, email, phone, dob FROM users WHERE username = ?');
    $requete->execute([$pseudo]);
    $userData = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        echo "Aucun utilisateur trouvé avec le pseudo : $pseudo";
    }
} else {
    echo "Pseudo non défini dans la session.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ANG Consulting - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #536967;
            --primary-light: #6a8482;
            --primary-dark: #3e504e;
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --accent-color: #536967;
            --accent-hover: #3e504e;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
        }

        [data-bs-theme="dark"] {
            --primary-color: #536967;
            --primary-light: #6a8482;
            --primary-dark: #3e504e;
            --bg-primary: #1e2624;
            --bg-secondary: #2a3532;
            --text-primary: #f3f4f6;
            --text-secondary: #d1d5db;
            --border-color: #374151;
            --accent-color: #536967;
            --accent-hover: #6a8482;
            --danger-color: #f87171;
            --success-color: #34d399;
            --warning-color: #fbbf24;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .bg-primary {
            background-color: var(--bg-primary);
        }

        .bg-secondary {
            background-color: var(--bg-secondary);
        }

        .text-primary {
            color: var(--text-primary);
        }

        .text-secondary {
            color: var(--text-secondary);
        }

        .border-color {
            border-color: var(--border-color);
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
        }

        .sidebar {
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .sidebar-collapsed {
            width: 8rem;
        }

        .sidebar-expanded {
            width: 19rem;
        }

        .nav-link {
            color: var(--text-secondary);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background-color: rgba(83, 105, 103, 0.1);
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .theme-dropdown {
            bottom: 100%;
            margin-bottom: 0.5rem;
        }

        /* Animation pour le chargement des cartes */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        /* Placeholder pour le logo */
        .logo-placeholder {
            width: 100%;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(83, 105, 103, 0.1);
            border-radius: 0.5rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Status indicator */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--success-color);
            position: absolute;
            bottom: 0;
            right: 0;
            border: 2px solid var(--bg-secondary);
        }

        /* Profile dropdown */
        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            width: 200px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 50;
            overflow: hidden;
        }

        .profile-dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            transition: all 0.2s ease;
        }

        .profile-dropdown-item:hover {
            background-color: rgba(83, 105, 103, 0.1);
        }

        .profile-dropdown-item.danger {
            color: var(--danger-color);
        }

        .profile-dropdown-item.danger:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        /* Empty state */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: var(--primary-color);
            opacity: 0.5;
            margin-bottom: 1rem;
        }

        /* Avatar upload */
        .avatar-upload {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
        }

        .avatar-preview {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            background-color: var(--primary-light);
            border: 3px solid var(--primary-color);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }

        .avatar-edit {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 36px;
            height: 36px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .avatar-edit input {
            display: none;
        }

        /* Toggle switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: var(--primary-color);
        }

        input:focus+.toggle-slider {
            box-shadow: 0 0 1px var(--primary-color);
        }

        input:checked+.toggle-slider:before {
            transform: translateX(24px);
        }

        /* Password strength */
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background-color: var(--danger-color);
            width: 30%;
        }

        .strength-medium {
            background-color: var(--warning-color);
            width: 60%;
        }

        .strength-strong {
            background-color: var(--success-color);
            width: 100%;
        }

        /* Form styles */
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(83, 105, 103, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }
    </style>
</head>

<body>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar sidebar-expanded h-full flex flex-col">
            <div class="p-4">
                <!-- Placeholder pour votre logo -->
                <div style="text-align: center;">
                    <img src="1708794619595.jpg" alt="Dashboard Preview" style="max-width: 50px; margin: 0 auto;">
                </div>

                <!-- Navigation -->
                <nav class="mt-6 space-y-2">
                    <a href="#" class="nav-link active flex items-center p-3" data-page="home">
                        <i class="fas fa-home w-6"></i>
                        <span class="ml-3 sidebar-text">Accueil</span>
                    </a>
                    <a href="#" class="nav-link flex items-center p-3" data-page="dashboard">
                        <i class="fas fa-chart-line w-6"></i>
                        <span class="ml-3 sidebar-text">Tableau de bord</span>
                    </a>

                    <!-- Prediction with submenu -->
                    <div class="nav-group">
                        <a href="#" class="flex items-center p-3" data-page="prediction">
                            <i class="fas fa-chart-bar w-6"></i>
                            <span class="ml-3 sidebar-text">Prédiction</span>
                        </a>
                        <div class="ml-8 mt-1 space-y-1">
                            <a href="#" class="nav-link block p-2 pl-4 text-sm text-gray-400 hover:text-gray-900"
                                data-page="model1">
                                <i class="fas fa-chart-bar w-6"></i>
                                <span class="ml-3 sidebar-text">Chiffre d'affaires annuel</span>
                            </a>
                            <a href="#" class="nav-link block p-2 pl-4 text-sm text-gray-400 hover:text-gray-900"
                                data-page="model2">
                                <i class="fas fa-chart-bar w-6"></i>
                                <span class="ml-3 sidebar-text">Nombre des factures</span>
                            </a>
                        </div>
                    </div>

                    <a href="#" class="nav-link flex items-center p-3" data-page="support">
                        <i class="fas fa-headset w-6"></i>
                        <span class="ml-3 sidebar-text">Support</span>
                    </a>

                    <a href="#" class="nav-link flex items-center p-3" data-page="settings">
                        <i class="fas fa-edit w-6"></i>
                        <span class="ml-3 sidebar-text">Modifier le profil</span>
                    </a>
                </nav>

            </div>

            <!-- Bouton pour réduire/agrandir la sidebar -->
            <div class="mt-auto p-4 border-t border-color">
                <button id="toggleSidebar"
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 text-secondary">
                    <i id="toggleIcon" class="fas fa-chevron-left"></i>
                    <span class="ml-2 sidebar-text">Réduire</span>
                </button>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-secondary border-b border-color p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="mobileMenuBtn" class="mr-4 lg:hidden text-primary">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 id="pageTitle" class="text-xl font-semibold text-primary">Accueil</h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Barre de recherche -->
                        <div class="hidden md:block relative">
                            <input type="text" placeholder="Rechercher..."
                                class="py-2 px-4 pr-10 rounded-lg border border-color bg-secondary text-primary focus:outline-none focus:ring-2 focus:ring-primary-light">
                            <i
                                class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                        </div>

                        <!-- Profil utilisateur -->
                        <div class="relative">
                            <button id="profileDropdownBtn" class="flex items-center space-x-2 focus:outline-none">
                                <div class="relative">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100">
                                        <i class="fas fa-user text-lg"></i>
                                    </div>
                                    <span class="status-indicator"></span>
                                </div>
                                <div class="hidden md:block">
                                    <p class="text-sm font-medium text-primary" id="userName">
                                        <?= htmlspecialchars($pseudo) ?>
                                    </p>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-secondary"></i>
                            </button>

                            <!-- Menu déroulant du profil -->
                            <div id="profileDropdown" class="profile-dropdown hidden">
                                <div class="p-4 border-b border-color">
                                    <p class="text-sm font-medium text-primary" id="userName">
                                        <?= htmlspecialchars($pseudo) ?>
                                    </p>
                                </div>
                                <div class="border-t border-color"></div>
                                <a href="logout.php" class="profile-dropdown-item danger">
                                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                    <span>Déconnexion</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenu du dashboard -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                <!-- Page d'accueil -->
                <div id="home-page" class="page-content hidden">
                    <div class="card p-6 mb-6">
                        <h2 class="text-2xl font-bold text-primary mb-4">Bienvenue sur votre tableau de bord</h2>
                        <p class="text-secondary mb-6">Visualisez, analysez et prédisez : votre avenir commercial
                            est entre vos mains !</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="card p-4 flex flex-col items-center text-center">
                                <div class="w-16 h-16 rounded-full  flex items-center justify-center mb-4"
                                    style="background-color : #B99644;">
                                    <i class="fas fa-chart-line text-xl"></i>
                                </div>
                                <h3 class="font-semibold text-primary mb-2">Analyser</h3>
                                <p class="text-secondary text-sm">Explorez vos données et découvrez des tendances</p>

                            </div>

                            <div class="card p-4 flex flex-col items-center text-center">
                                <div class="w-16 h-16 rounded-full  flex items-center justify-center mb-4"
                                    style="background-color : #B99644;">
                                    <i class="fas fa-chart-bar text-xl"></i>
                                </div>

                                <h3 class="font-semibold text-primary mb-2">Prédire</h3>
                                <p class="text-secondary text-sm">Anticipez vos ventes et prenez de meilleures décisions
                                    grâce à nos solutions prédictives</p>
                            </div>

                            <div class="card p-4 flex flex-col items-center text-center">
                                <div class="w-16 h-16 rounded-full  flex items-center justify-center mb-4"
                                    style="background-color : #B99644;">
                                    <i class="fas fa-headset w-6"></i>
                                </div>

                                <h3 class="font-semibold text-primary mb-2">Support</h3>
                                <p class="text-secondary text-sm">Résolvez vos problèmes rapidement grâce à notre
                                    assistance personnalisée
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center md:items-start gap-8 card p-6">
                        <!-- Texte à gauche -->

                        <div class="md:w-1/2">

                            <h2 class="text-2xl font-bold text-primary mb-4">Sales Predictive Analytics</h2>
                            <p class="text-secondary mb-6">
                                Nous développons une solution de <strong>prédiction des ventes</strong> pour le compte
                                d’un
                                cabinet d’expertise comptable, client de Bee Coders.<br>
                                L’objectif : utiliser la <strong>Business Intelligence</strong> et le <strong>Machine
                                    Learning</strong> pour anticiper les tendances de vente
                                d’une entreprise à partir de ses données historiques, de ses revenus et de ses
                                transactions
                                de facturation.<br><br>

                                Ce modèle prédictif permet d’optimiser la <strong>gestion des stocks</strong>, d’ajuster
                                la
                                <strong>stratégie commerciale</strong>
                                et de mieux prévoir la <strong>demande du marché</strong>.<br><br>

                                En complément, un <strong>tableau de bord interactif</strong> est conçu pour visualiser
                                les
                                chiffres clés : variations du chiffre d’affaires,
                                volumes de vente, transactions et flux financiers, avec une lecture simple, claire et
                                dynamique.
                            </p>
                        </div>

                        <!-- Image à droite -->
                        <div class="md:w-1/2">
                            <svg class="animated" id="freepik_stories-investment-data"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" version="1.1"
                                xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs">
                                <style>
                                    svg#freepik_stories-investment-data:not(.animated) .animable {
                                        opacity: 0;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--Floor--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--Plant--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--Gears--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--coins-2--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--Arrow--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--bar-chart--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--pie-charts--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--coins-1--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--magnifying-glass--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--curve-chart--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    svg#freepik_stories-investment-data.animated #freepik--speech-bubbles--inject-15 {
                                        animation: 3s Infinite linear floating;
                                        animation-delay: 0s;
                                    }

                                    @keyframes floating {
                                        0% {
                                            opacity: 1;
                                            transform: translateY(0px);
                                        }

                                        50% {
                                            transform: translateY(-10px);
                                        }

                                        100% {
                                            opacity: 1;
                                            transform: translateY(0px);
                                        }
                                    }

                                    .animator-hidden {
                                        display: none;
                                    }
                                </style>
                                <g id="freepik--Floor--inject-15" class="animable"
                                    style="transform-origin: 249.996px 321.54px;">
                                    <path id="freepik--floor--inject-15"
                                        d="M76,437.23c-96.1-63.89-96.1-167.49,0-231.38s251.92-63.9,348,0,96.1,167.49,0,231.38S172.09,501.13,76,437.23Z"
                                        style="fill: rgb(245, 245, 245); transform-origin: 249.996px 321.54px;"
                                        class="animable"></path>
                                </g>
                                <g id="freepik--Plant--inject-15" class="animable"
                                    style="transform-origin: 448.989px 203.105px;">
                                    <g id="freepik--Plants--inject-15" class="animable"
                                        style="transform-origin: 448.989px 203.105px;">
                                        <path
                                            d="M436.39,237.69l-8.78-5.07s-5.22-21.69-3.14-41.21c2.93-27.45,21-37.78,27.86-35.41s8.86,10.35-2.5,26.7C443.12,192.35,434.88,212.44,436.39,237.69Z"
                                            style="fill: #314A48; transform-origin: 440.947px 196.681px;"
                                            id="el40fjtb87kmo" class="animable"></path>
                                        <g id="eli4onxgztwgt">
                                            <g style="opacity: 0.3; transform-origin: 440.947px 196.681px;"
                                                class="animable" id="el3sl2g7n20if">
                                                <path
                                                    d="M436.39,237.69l-8.78-5.07s-5.22-21.69-3.14-41.21c2.93-27.45,21-37.78,27.86-35.41s8.86,10.35-2.5,26.7C443.12,192.35,434.88,212.44,436.39,237.69Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 440.947px 196.681px;"
                                                    id="elm27hpt772xp" class="animable"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M432.75,227h.08a.63.63,0,0,0,.49-.72c-4.81-25.31,4-53.76,15-65.6a.6.6,0,0,0,0-.86.61.61,0,0,0-.86,0c-11.15,12.05-20.17,41-15.29,66.65A.62.62,0,0,0,432.75,227Z"
                                            style="fill: rgb(255, 255, 255); transform-origin: 439.657px 193.321px;"
                                            id="el4e00k0vfv6" class="animable"></path>
                                        <path
                                            d="M458.46,250.44c1.31.67,4.8-2.31,5.77-3a12.19,12.19,0,0,0,3.81-4.84,11.4,11.4,0,0,0,.67-6.9,28.33,28.33,0,0,0-1-3.59c-.4-1.16-.8-2.32-1.19-3.48a8.91,8.91,0,0,1-.7-3.54c.19-2.43,2.24-4.23,4.12-5.78,2.07-1.73,4.21-4,4.05-6.88a10.21,10.21,0,0,0-2.87-5.95c-1.48-1.7-3.21-3.17-4.56-5a7.19,7.19,0,0,1-1.69-4.45,7.38,7.38,0,0,1,.85-2.87c.93-1.94,2.19-3.75,2.8-5.81s.38-4.6-1.28-6c-1.37-1.13-3.31-1.2-5.07-1a11.44,11.44,0,0,0-4.57,1.88c-4.49,2.8-6.85,8.4-11.64,10.62a50.7,50.7,0,0,1-5.86,1.75c-1.94.61-3.9,1.72-4.72,3.59-1.62,3.68,1.94,7.94.87,11.81-.95,3.43-4.16,4.89-5.85,8a7.26,7.26,0,0,0-.63,4.46,21.25,21.25,0,0,0,2.23,7.37,67.24,67.24,0,0,0,4.44,6.81S458.26,250.34,458.46,250.44Z"
                                            style="fill: #314A48; transform-origin: 451.829px 215.9px;"
                                            id="elaxpg1x9d0s" class="animable"></path>
                                        <g id="elj22k7simgif">
                                            <g style="opacity: 0.65; transform-origin: 451.829px 215.9px;"
                                                class="animable" id="el6a000vypc8u">
                                                <path
                                                    d="M458.46,250.44c1.31.67,4.8-2.31,5.77-3a12.19,12.19,0,0,0,3.81-4.84,11.4,11.4,0,0,0,.67-6.9,28.33,28.33,0,0,0-1-3.59c-.4-1.16-.8-2.32-1.19-3.48a8.91,8.91,0,0,1-.7-3.54c.19-2.43,2.24-4.23,4.12-5.78,2.07-1.73,4.21-4,4.05-6.88a10.21,10.21,0,0,0-2.87-5.95c-1.48-1.7-3.21-3.17-4.56-5a7.19,7.19,0,0,1-1.69-4.45,7.38,7.38,0,0,1,.85-2.87c.93-1.94,2.19-3.75,2.8-5.81s.38-4.6-1.28-6c-1.37-1.13-3.31-1.2-5.07-1a11.44,11.44,0,0,0-4.57,1.88c-4.49,2.8-6.85,8.4-11.64,10.62a50.7,50.7,0,0,1-5.86,1.75c-1.94.61-3.9,1.72-4.72,3.59-1.62,3.68,1.94,7.94.87,11.81-.95,3.43-4.16,4.89-5.85,8a7.26,7.26,0,0,0-.63,4.46,21.25,21.25,0,0,0,2.23,7.37,67.24,67.24,0,0,0,4.44,6.81S458.26,250.34,458.46,250.44Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 451.829px 215.9px;"
                                                    id="elvvs066cwc2o" class="animable"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M445.77,243.82h0a.57.57,0,0,0,.53-.62c-2.1-22.77,7.38-48.51,14.83-56.22a.58.58,0,0,0-.83-.8c-7.6,7.87-17.28,34-15.15,57.12A.58.58,0,0,0,445.77,243.82Z"
                                            style="fill: rgb(255, 255, 255); transform-origin: 453.05px 214.934px;"
                                            id="elzjt6971xlb" class="animable"></path>
                                        <path
                                            d="M448,215.75l.17,0a77.59,77.59,0,0,1,16.42-3.5.57.57,0,0,0,.52-.63.56.56,0,0,0-.63-.52,78.57,78.57,0,0,0-16.71,3.57.57.57,0,0,0-.34.74A.58.58,0,0,0,448,215.75Z"
                                            style="fill: rgb(255, 255, 255); transform-origin: 456.253px 213.423px;"
                                            id="eldlk53o9dyso" class="animable"></path>
                                    </g>
                                </g>
                                <g id="freepik--Shadows--inject-15" class="animable"
                                    style="transform-origin: 245.013px 345.829px;">
                                    <ellipse id="freepik--Shadow--inject-15" cx="417.68" cy="258.71" rx="48.01"
                                        ry="27.72"
                                        style="fill: rgb(224, 224, 224); transform-origin: 417.68px 258.71px;"
                                        class="animable"></ellipse>
                                    <path id="freepik--shadow--inject-15"
                                        d="M108.09,358.28c-19.16-11.06-50.22-11.06-69.38,0s-19.17,29,0,40.06,50.22,11.06,69.38,0S127.25,369.34,108.09,358.28Z"
                                        style="fill: rgb(224, 224, 224); transform-origin: 73.3981px 378.31px;"
                                        class="animable"></path>
                                    <path id="freepik--shadow--inject-15"
                                        d="M177.44,410.44c-19.9-11.49-52.16-11.49-72.07,0s-19.9,30.12,0,41.61,52.17,11.49,72.07,0S197.34,421.93,177.44,410.44Z"
                                        style="fill: rgb(224, 224, 224); transform-origin: 141.403px 431.245px;"
                                        class="animable"></path>
                                </g>
                                <g id="freepik--Gears--inject-15" class="animable"
                                    style="transform-origin: 418.642px 85.9632px;">
                                    <g id="freepik--Gear--inject-15" class="animable"
                                        style="transform-origin: 418.642px 85.9632px;">
                                        <path id="freepik--gear--inject-15"
                                            d="M432.32,60.65V52.87a.5.5,0,0,0-.76-.5l-6,2.71a.71.71,0,0,1-1-.47,13,13,0,0,0-1.33-3,1.61,1.61,0,0,1-.09-1.45l4-8.06a.9.9,0,0,0-.35-1.17l-4.34-2.5a.89.89,0,0,0-1.19.28l-5,7.48a1.66,1.66,0,0,1-1.3.65,13.29,13.29,0,0,0-3.25.33.71.71,0,0,1-.9-.63L410.17,40a.5.5,0,0,0-.81-.41l-6.74,3.89a1.8,1.8,0,0,0-.8,1.25l-.68,7.5a2.53,2.53,0,0,1-.7,1.4,39.22,39.22,0,0,0-3.74,4,1.28,1.28,0,0,1-1.32.37l-4.33-1.51a1.06,1.06,0,0,0-1.21.45l-4.95,8.58a1.05,1.05,0,0,0,.22,1.28l3.47,3a1.3,1.3,0,0,1,.33,1.34,40.86,40.86,0,0,0-1.56,5.22,2.46,2.46,0,0,1-.86,1.3L380.34,82a1.82,1.82,0,0,0-.69,1.33v7.78a.5.5,0,0,0,.77.49l6-2.7a.71.71,0,0,1,1,.46,13.46,13.46,0,0,0,1.34,3,1.7,1.7,0,0,1,.09,1.46l-4,8.05a.91.91,0,0,0,.36,1.17l4.33,2.51a.92.92,0,0,0,1.2-.28l5-7.49a1.64,1.64,0,0,1,1.3-.65,13.21,13.21,0,0,0,3.24-.33.72.72,0,0,1,.9.64l.66,6.54a.5.5,0,0,0,.81.42l6.74-3.89a1.84,1.84,0,0,0,.8-1.26l.67-7.49a2.47,2.47,0,0,1,.7-1.4,41.47,41.47,0,0,0,3.74-4,1.31,1.31,0,0,1,1.33-.38l4.32,1.51a1.05,1.05,0,0,0,1.21-.45l5-8.57a1.06,1.06,0,0,0-.22-1.28l-3.47-3a1.29,1.29,0,0,1-.34-1.33,40.22,40.22,0,0,0,1.56-5.22,2.48,2.48,0,0,1,.86-1.31L431.64,62A1.81,1.81,0,0,0,432.32,60.65ZM406,85.1c-6.29,3.63-11.38.69-11.38-6.57S399.7,62.45,406,58.82s11.38-.68,11.38,6.57S412.27,81.47,406,85.1Z"
                                            style="fill: rgb(235, 235, 235); transform-origin: 405.985px 71.9872px;"
                                            class="animable"></path>
                                        <path id="freepik--gear--inject-15"
                                            d="M457.62,94.47l-.72-4.16a.69.69,0,0,0-1.14-.5L452,92.32a1.11,1.11,0,0,1-1.54-.34,8.37,8.37,0,0,0-.74-.91,1.89,1.89,0,0,1-.45-1.82L451.17,84a1,1,0,0,0-.71-1.31L447.58,82a1.55,1.55,0,0,0-1.63.76l-2.71,5.15a2.8,2.8,0,0,1-1.63,1.23,13.72,13.72,0,0,0-1.78.59,1,1,0,0,1-1.36-.63l-1-3.22a.72.72,0,0,0-1.22-.39L432,88.91a2.43,2.43,0,0,0-.82,1.83l.26,4.1a3.25,3.25,0,0,1-.67,2,30.63,30.63,0,0,0-2,2.65,2.23,2.23,0,0,1-1.76.89l-2.77-.14a1.77,1.77,0,0,0-1.59,1L420,107.05a1.12,1.12,0,0,0,.58,1.48l2.24,1a1.37,1.37,0,0,1,.77,1.54,25.46,25.46,0,0,0-.48,2.79,3.46,3.46,0,0,1-.93,1.89l-3.83,3.52a2.32,2.32,0,0,0-.64,1.88l.72,4.17a.69.69,0,0,0,1.14.49l3.82-2.51a1.1,1.1,0,0,1,1.53.34,8.37,8.37,0,0,0,.74.91,1.9,1.9,0,0,1,.45,1.82l-1.95,5.27a1,1,0,0,0,.72,1.31l2.87.64a1.55,1.55,0,0,0,1.63-.75l2.71-5.16a2.83,2.83,0,0,1,1.64-1.23,13.6,13.6,0,0,0,1.77-.59,1,1,0,0,1,1.36.63l1,3.23a.72.72,0,0,0,1.22.38l4.31-3.39a2.43,2.43,0,0,0,.82-1.83l-.26-4.1a3.25,3.25,0,0,1,.67-2,32.92,32.92,0,0,0,2-2.65,2.21,2.21,0,0,1,1.75-.89l2.77.14a1.8,1.8,0,0,0,1.6-1l2.6-5.84a1.1,1.1,0,0,0-.58-1.48l-2.24-1a1.38,1.38,0,0,1-.77-1.54,23.28,23.28,0,0,0,.48-2.79,3.47,3.47,0,0,1,.94-1.89L457,96.35A2.29,2.29,0,0,0,457.62,94.47ZM439.2,116.68c-4.67,3.67-9.15,2.67-10-2.24s2.26-11.86,6.93-15.54,9.15-2.67,10,2.24S443.88,113,439.2,116.68Z"
                                            style="fill: rgb(235, 235, 235); transform-origin: 437.667px 107.795px;"
                                            class="animable"></path>
                                    </g>
                                </g>
                                <g id="freepik--coins-2--inject-15" class="animable"
                                    style="transform-origin: 418.43px 246.331px;">
                                    <g id="freepik--Coins--inject-15" class="animable"
                                        style="transform-origin: 418.43px 246.331px;">
                                        <path
                                            d="M460,248.38A27.42,27.42,0,0,0,449.83,239c-16.58-9.57-43.46-9.57-60,0a27.34,27.34,0,0,0-10.14,9.37h-2.3v8.68h0c.31,6,4.44,12,12.41,16.61,16.57,9.57,43.45,9.57,60,0,8-4.61,12.13-10.58,12.43-16.61h0v-8.68Z"
                                            style="fill: #314A48; transform-origin: 419.81px 256.33px;"
                                            id="eluu4kcxy85k" class="animable"></path>
                                        <g id="el05znpae6fsv6">
                                            <g style="opacity: 0.25; transform-origin: 419.81px 256.33px;"
                                                class="animable" id="elfiq7rzuxdct">
                                                <path
                                                    d="M460,248.38A27.42,27.42,0,0,0,449.83,239c-16.58-9.57-43.46-9.57-60,0a27.34,27.34,0,0,0-10.14,9.37h-2.3v8.68h0c.31,6,4.44,12,12.41,16.61,16.57,9.57,43.45,9.57,60,0,8-4.61,12.13-10.58,12.43-16.61h0v-8.68Z"
                                                    id="elnstuca6m99b" class="animable"
                                                    style="transform-origin: 419.81px 256.33px;"></path>
                                            </g>
                                        </g>
                                        <g id="elfdema7e3sbg">
                                            <path
                                                d="M419.82,231.83c-10.87,0-21.73,2.39-30,7.18a27.34,27.34,0,0,0-10.14,9.37h-2.3v8.68h0c.31,6,4.44,12,12.41,16.61,8.29,4.78,19.15,7.17,30,7.17Z"
                                                style="opacity: 0.1; transform-origin: 398.6px 256.335px;"
                                                class="animable" id="elctolh4fxlfu"></path>
                                        </g>
                                        <g id="eltzsvl9z3a8b">
                                            <path
                                                d="M396.63,235.81a45.54,45.54,0,0,0-6.83,3.2,27.34,27.34,0,0,0-10.14,9.37h-2.3v8.68h0c.31,6,4.44,12,12.41,16.61a47.6,47.6,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 386.995px 256.34px;"
                                                class="animable" id="el2uv7sfj7w8n"></path>
                                        </g>
                                        <g id="elkv8mjjs5cir">
                                            <path
                                                d="M443,235.81a46,46,0,0,1,6.83,3.2A27.42,27.42,0,0,1,460,248.38h2.29v8.68h0c-.3,6-4.46,12-12.43,16.61a48.11,48.11,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 452.645px 256.34px;"
                                                class="animable" id="elxwyvvgfjaml"></path>
                                        </g>
                                        <path
                                            d="M449.83,231.06c-16.58-9.57-43.46-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.57,60,0S466.41,240.63,449.83,231.06Z"
                                            style="fill: #314A48; transform-origin: 419.832px 248.39px;"
                                            id="elquo2084o10n" class="animable"></path>
                                        <g id="elcrovmq4q996">
                                            <path
                                                d="M449.83,231.06c-16.58-9.57-43.46-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.57,60,0S466.41,240.63,449.83,231.06Z"
                                                style="opacity: 0.2; transform-origin: 419.832px 248.39px;"
                                                class="animable" id="elp6cx490h2vo"></path>
                                        </g>
                                        <path
                                            d="M439.36,237.4c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.85-8.62,6.75-8.62,11s3.06,8.14,8.62,11c5.24,2.7,12.19,4.18,19.54,4.18s14.3-1.48,19.55-4.18c5.55-2.84,8.61-6.74,8.61-11S444.91,240.25,439.36,237.4Z"
                                            style="fill: #314A48; transform-origin: 419.81px 248.405px;"
                                            id="elw3d8fm1lnps" class="animable"></path>
                                        <g id="eluatprtsflb">
                                            <path
                                                d="M439.36,237.4c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.85-8.62,6.75-8.62,11s3.06,8.14,8.62,11c5.24,2.7,12.19,4.18,19.54,4.18s14.3-1.48,19.55-4.18c5.55-2.84,8.61-6.74,8.61-11S444.91,240.25,439.36,237.4Z"
                                                style="opacity: 0.4; transform-origin: 419.81px 248.405px;"
                                                class="animable" id="elj575s25hjwj"></path>
                                        </g>
                                        <path
                                            d="M448.91,232.66c-7.74-4.47-18.08-6.94-29.1-6.94s-21.35,2.47-29.09,6.94c-7.42,4.29-11.5,9.86-11.5,15.73s4.08,11.44,11.5,15.73c7.74,4.46,18.07,6.93,29.09,6.93s21.36-2.47,29.1-6.93c7.41-4.29,11.51-9.87,11.51-15.73S456.32,237,448.91,232.66Zm-9.55,26.71c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.54-4.18c-5.56-2.84-8.62-6.74-8.62-11s3.06-8.14,8.62-11c5.24-2.69,12.19-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.75,8.61,11S444.91,256.53,439.36,259.37Z"
                                            style="fill: #314A48; transform-origin: 419.82px 248.385px;"
                                            id="elg3y6zasdxq9" class="animable"></path>
                                        <g id="elstdemzxbsmh">
                                            <path
                                                d="M448.91,232.66c-7.74-4.47-18.08-6.94-29.1-6.94s-21.35,2.47-29.09,6.94c-7.42,4.29-11.5,9.86-11.5,15.73s4.08,11.44,11.5,15.73c7.74,4.46,18.07,6.93,29.09,6.93s21.36-2.47,29.1-6.93c7.41-4.29,11.51-9.87,11.51-15.73S456.32,237,448.91,232.66Zm-9.55,26.71c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.54-4.18c-5.56-2.84-8.62-6.74-8.62-11s3.06-8.14,8.62-11c5.24-2.69,12.19-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.75,8.61,11S444.91,256.53,439.36,259.37Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 419.82px 248.385px;"
                                                class="animable" id="eltqhgc852dy"></path>
                                        </g>
                                        <g id="el1lma5xfo9ly">
                                            <path
                                                d="M438.69,258.07c10.43-5.35,10.43-14,0-19.37s-27.33-5.35-37.76,0-10.42,14,0,19.37S428.27,263.42,438.69,258.07Z"
                                                style="opacity: 0.2; transform-origin: 419.812px 248.385px;"
                                                class="animable" id="eldqeax57m3r7"></path>
                                        </g>
                                        <path
                                            d="M438.69,258.07c10.43-5.35,10.43-14,0-19.37s-27.33-5.35-37.76,0-10.42,14,0,19.37S428.27,263.42,438.69,258.07Z"
                                            style="fill: #314A48; transform-origin: 419.812px 248.385px;"
                                            id="elqcr4pu4r85" class="animable"></path>
                                        <g id="elnhr42uvjhi">
                                            <path
                                                d="M438.69,258.07c10.43-5.35,10.43-14,0-19.37s-27.33-5.35-37.76,0-10.42,14,0,19.37S428.27,263.42,438.69,258.07Z"
                                                style="opacity: 0.45; transform-origin: 419.812px 248.385px;"
                                                class="animable" id="elxa3oz2t7rvf"></path>
                                        </g>
                                        <path
                                            d="M438.69,242.33c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.21-6.83,5-7.58,7.87.75,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.34,5.35,37.76,0,4.31-2.21,6.84-5,7.58-7.87C445.53,247.32,443,244.54,438.69,242.33Z"
                                            style="fill: #314A48; transform-origin: 419.81px 250.2px;"
                                            id="el5vj5n78855i" class="animable"></path>
                                        <g id="eltketjwsfzzd">
                                            <path
                                                d="M438.69,242.33c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.21-6.83,5-7.58,7.87.75,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.34,5.35,37.76,0,4.31-2.21,6.84-5,7.58-7.87C445.53,247.32,443,244.54,438.69,242.33Z"
                                                style="opacity: 0.3; transform-origin: 419.81px 250.2px;"
                                                class="animable" id="elk0tk8wo443l"></path>
                                        </g>
                                        <polygon
                                            points="422.34 238.91 422.34 242.82 421 244.23 418.53 246.82 418.53 242.75 422.34 238.91"
                                            style="fill: #314A48; transform-origin: 420.435px 242.865px;"
                                            id="elqoo53ecml7q" class="animable"></polygon>
                                        <g id="elew7jliow5fa">
                                            <polygon
                                                points="422.34 238.91 422.34 242.82 421 244.23 418.53 246.82 418.53 242.75 422.34 238.91"
                                                style="opacity: 0.3; transform-origin: 420.435px 242.865px;"
                                                class="animable" id="elq9gfkhurywk"></polygon>
                                        </g>
                                        <path
                                            d="M418.53,242.74v2.31a20.38,20.38,0,0,1-3.8,1.08,3.93,3.93,0,0,1-1.62-.06,3.29,3.29,0,0,1-.78-.32c-.94-.56-1.09-1.4.7-2.43A6.25,6.25,0,0,1,418.53,242.74Z"
                                            style="fill: #314A48; transform-origin: 415.088px 244.271px;"
                                            id="elkagnshypa1" class="animable"></path>
                                        <g id="ell727viuctx">
                                            <path
                                                d="M418.53,242.74v2.31a20.38,20.38,0,0,1-3.8,1.08,3.93,3.93,0,0,1-1.62-.06,3.29,3.29,0,0,1-.78-.32c-.94-.56-1.09-1.4.7-2.43A6.25,6.25,0,0,1,418.53,242.74Z"
                                                style="opacity: 0.45; transform-origin: 415.088px 244.271px;"
                                                class="animable" id="el13er8vsso94g"></path>
                                        </g>
                                        <path
                                            d="M436.25,253.86v3.38L432,259.71l-3.16-1.83a16.13,16.13,0,0,1-12.27.55v-3.38l1.95-1.93-1.95.54a12.54,12.54,0,0,1-9.55-1.1c-1.92-1.1-2.81-2.32-2.81-3.56v-3.29l.35-1.44-1.17-.68v-3.38l3.17,1.83c-1.55,1.2-2.37,2.43-2.35,3.63v0c0,1.21.94,2.39,2.81,3.48,4.17,2.4,8.29,1.61,12.07.39,3.52-1.14,6.08-2.49,8.1-1.32,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4,4a14.93,14.93,0,0,0,2.24.66,16.77,16.77,0,0,0,10-1.21l3.16,1.83Z"
                                            style="fill: #314A48; transform-origin: 419.82px 249.96px;"
                                            id="ela6x0leiseg" class="animable"></path>
                                        <g id="elcd1h1asr8v8">
                                            <path
                                                d="M404.18,245.67v0c0,1.21.94,2.39,2.81,3.48,4.17,2.4,8.29,1.61,12.07.39,3.52-1.14,6.08-2.49,8.1-1.32,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4,4a14.93,14.93,0,0,0,2.24.66,16.77,16.77,0,0,0,10-1.21l3.16,1.83,4.28-2.47v3.38L432,259.71l-3.16-1.83a16.13,16.13,0,0,1-12.27.55v-3.38l1.95-1.93-1.95.54a12.54,12.54,0,0,1-9.55-1.1c-1.92-1.1-2.81-2.32-2.81-3.56v-3.29l.35-1.44"
                                                style="opacity: 0.2; transform-origin: 420.245px 251.99px;"
                                                class="animable" id="elkzbxx5yjq19"></path>
                                        </g>
                                        <g id="el0uaf3ftutkok">
                                            <path d="M403.36,240.21v3.38l1.17.68h0a7,7,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 404.945px 242.24px;"
                                                class="animable" id="el3k2awjf3yvv"></path>
                                        </g>
                                        <path
                                            d="M435.55,251.65a4.08,4.08,0,0,1-.32,1.61L433.08,252a4.2,4.2,0,0,0,2.43-3.25C435.53,249.71,435.55,251.56,435.55,251.65Z"
                                            style="fill: #314A48; transform-origin: 434.315px 251.005px;"
                                            id="elfn14uhm1qvn" class="animable"></path>
                                        <g id="elo2huxlpirbf">
                                            <g style="opacity: 0.3; transform-origin: 434.315px 251.005px;"
                                                class="animable" id="elyblnlvw6j3a">
                                                <path
                                                    d="M435.55,251.65a4.08,4.08,0,0,1-.32,1.61L433.08,252a4.2,4.2,0,0,0,2.43-3.25C435.53,249.71,435.55,251.56,435.55,251.65Z"
                                                    id="elydhnwvdb5q" class="animable"
                                                    style="transform-origin: 434.315px 251.005px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M435.54,253.45,433.09,252a4.18,4.18,0,0,0,2.45-3.76c0-.11,0-.22,0-.33-.11-1.16-1-2.3-2.74-3.32h0a11.27,11.27,0,0,0-5.7-1.48h-.54l-.51,0-.44,0-.18,0-.34.05a21.9,21.9,0,0,0-2.77.57c-.44.12-.87.24-1.29.38-.62.19-1.23.39-1.8.59l-.68.23a21.36,21.36,0,0,1-3.79,1.08,4,4,0,0,1-1.64-.06,3.61,3.61,0,0,1-.76-.32c-1-.55-1.11-1.39.69-2.43a6.25,6.25,0,0,1,5.5-.57l3.81-3.84a16.32,16.32,0,0,0-11.49.63l-3.17-1.82-4.32,2.49,3.18,1.83c-1.55,1.19-2.38,2.44-2.36,3.63v0c0,1.22.93,2.39,2.81,3.48,4.17,2.4,8.3,1.61,12.07.39,3.52-1.15,6.07-2.49,8.1-1.33,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4,4a15.29,15.29,0,0,0,2.24.66,14.89,14.89,0,0,0,3.9.29,18.28,18.28,0,0,0,6.12-1.5l3.17,1.84,4.28-2.48Z"
                                            style="fill: #314A48; transform-origin: 419.87px 246.88px;"
                                            id="eldf73awk19tg" class="animable"></path>
                                        <g id="ela64c59lvu4l">
                                            <g style="opacity: 0.3; transform-origin: 419.87px 246.88px;"
                                                class="animable" id="el6uwh2rnx4yn">
                                                <path
                                                    d="M435.54,253.45,433.09,252a4.18,4.18,0,0,0,2.45-3.76c0-.11,0-.22,0-.33-.11-1.16-1-2.3-2.74-3.32h0a11.27,11.27,0,0,0-5.7-1.48h-.54l-.51,0-.44,0-.18,0-.34.05a21.9,21.9,0,0,0-2.77.57c-.44.12-.87.24-1.29.38-.62.19-1.23.39-1.8.59l-.68.23a21.36,21.36,0,0,1-3.79,1.08,4,4,0,0,1-1.64-.06,3.61,3.61,0,0,1-.76-.32c-1-.55-1.11-1.39.69-2.43a6.25,6.25,0,0,1,5.5-.57l3.81-3.84a16.32,16.32,0,0,0-11.49.63l-3.17-1.82-4.32,2.49,3.18,1.83c-1.55,1.19-2.38,2.44-2.36,3.63v0c0,1.22.93,2.39,2.81,3.48,4.17,2.4,8.3,1.61,12.07.39,3.52-1.15,6.07-2.49,8.1-1.33,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4,4a15.29,15.29,0,0,0,2.24.66,14.89,14.89,0,0,0,3.9.29,18.28,18.28,0,0,0,6.12-1.5l3.17,1.84,4.28-2.48Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 419.87px 246.88px;"
                                                    id="eloqh4s0ncgoq" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="428.8 254.5 428.8 257.88 431.98 259.71 431.98 256.33 428.8 254.5"
                                            style="fill: #314A48; transform-origin: 430.39px 257.105px;"
                                            id="elw4dnijzdood" class="animable"></polygon>
                                        <g id="elzwtpp8agrh9">
                                            <polygon
                                                points="428.8 254.5 428.8 257.88 431.98 259.71 431.98 256.33 428.8 254.5"
                                                style="opacity: 0.45; transform-origin: 430.39px 257.105px;"
                                                class="animable" id="eld7e20ermrl7"></polygon>
                                        </g>
                                        <g id="el67nacqu1g9l">
                                            <polygon
                                                points="403.36 240.21 407.68 237.72 410.85 239.54 407.71 238.23 403.36 240.21"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 407.105px 238.965px;"
                                                class="animable" id="el320p420j0qh"></polygon>
                                        </g>
                                        <g id="el87hzx7z4pth">
                                            <path
                                                d="M413,243.32a6.25,6.25,0,0,1,5.5-.57l3.81-3.84-3.81,3.44A5.76,5.76,0,0,0,413,243.32Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 417.655px 241.115px;"
                                                class="animable" id="elwdufvvr17c"></path>
                                        </g>
                                        <g id="el5uwu59fhkwh">
                                            <path
                                                d="M416.54,255.05a15.29,15.29,0,0,0,2.24.66,14.89,14.89,0,0,0,3.9.29,13.76,13.76,0,0,1-5.5-1.1l3.41-3.87Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 419.61px 253.527px;"
                                                class="animable" id="elp0ftulkcf6o"></path>
                                        </g>
                                        <g id="elciomu4704og">
                                            <polygon
                                                points="428.8 254.5 431.98 255.93 436.25 253.86 431.98 256.33 428.8 254.5"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 432.525px 255.095px;"
                                                class="animable" id="el41956i26xdd"></polygon>
                                        </g>
                                        <g id="els1ujv91m0xn">
                                            <path
                                                d="M391.05,233.23c11.24-6.83,28.76-7.51,28.76-7.51-11,0-21.35,2.47-29.09,6.94-7.42,4.29-11.5,9.86-11.5,15.73C379.22,248.39,379.62,240.17,391.05,233.23Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 399.515px 237.055px;"
                                                class="animable" id="elozj3owqp0i"></path>
                                        </g>
                                        <g id="el8u0jdkm5jpv">
                                            <path
                                                d="M416.54,272.82c11.93.53,24.17-1.84,33.29-7.1,9.49-5.48,13.54-12.9,12.17-20.05,0,0,1.77,11.64-12.89,19.61S416.54,272.82,416.54,272.82Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 439.4px 259.281px;"
                                                class="animable" id="el57y58hydyqu"></path>
                                        </g>
                                        <path
                                            d="M457.18,236.36A27.28,27.28,0,0,0,447,227c-16.58-9.57-43.45-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38h-2.29V245h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.45,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                            style="fill: #314A48; transform-origin: 416.99px 244.305px;"
                                            id="eljng2dsydjo" class="animable"></path>
                                        <g id="elts38l1ka6c8">
                                            <g style="opacity: 0.25; transform-origin: 416.99px 244.305px;"
                                                class="animable" id="elkpkj86u8jc">
                                                <path
                                                    d="M457.18,236.36A27.28,27.28,0,0,0,447,227c-16.58-9.57-43.45-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38h-2.29V245h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.45,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                                    id="el871vtzv5s4k" class="animable"
                                                    style="transform-origin: 416.99px 244.305px;"></path>
                                            </g>
                                        </g>
                                        <g id="elgkva3p4ywnh">
                                            <path
                                                d="M417,219.8c-10.87,0-21.73,2.39-30,7.18a27.36,27.36,0,0,0-10.14,9.38h-2.29V245h0c.3,6,4.43,12,12.4,16.61,8.29,4.79,19.15,7.18,30,7.18Z"
                                                style="opacity: 0.1; transform-origin: 395.785px 244.295px;"
                                                class="animable" id="elc5zx7jm15sa"></path>
                                        </g>
                                        <g id="el5jb37s75g3r">
                                            <path
                                                d="M393.84,223.78A47,47,0,0,0,387,227a27.36,27.36,0,0,0-10.14,9.38h-2.29V245h0c.3,6,4.43,12,12.4,16.61a46,46,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 384.205px 244.295px;"
                                                class="animable" id="elgw9inyrg5bs"></path>
                                        </g>
                                        <g id="eld94sg5k3jhc">
                                            <path
                                                d="M440.21,223.78A46.55,46.55,0,0,1,447,227a27.28,27.28,0,0,1,10.14,9.38h2.3V245h0c-.31,6-4.47,12-12.44,16.61a45.54,45.54,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 449.805px 244.295px;"
                                                class="animable" id="elrw09yv80y5r"></path>
                                        </g>
                                        <path
                                            d="M447,219c-16.58-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.57,60,0S463.62,228.6,447,219Z"
                                            style="fill: #314A48; transform-origin: 417.008px 236.33px;"
                                            id="elnqny933nnbg" class="animable"></path>
                                        <g id="eldbux5kkmpft">
                                            <path
                                                d="M447,219c-16.58-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.57,60,0S463.62,228.6,447,219Z"
                                                style="opacity: 0.2; transform-origin: 417.008px 236.33px;"
                                                class="animable" id="elkh8l2vf4sx"></path>
                                        </g>
                                        <path
                                            d="M436.57,225.37c-5.25-2.68-12.18-4.17-19.55-4.17s-14.3,1.49-19.54,4.17c-5.56,2.85-8.62,6.76-8.62,11s3.06,8.14,8.62,11c5.24,2.69,12.19,4.18,19.54,4.18s14.3-1.49,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S442.12,228.22,436.57,225.37Z"
                                            style="fill: #314A48; transform-origin: 417.02px 236.375px;"
                                            id="el9jztywyv28" class="animable"></path>
                                        <g id="elvjg3b9teuhc">
                                            <path
                                                d="M436.57,225.37c-5.25-2.68-12.18-4.17-19.55-4.17s-14.3,1.49-19.54,4.17c-5.56,2.85-8.62,6.76-8.62,11s3.06,8.14,8.62,11c5.24,2.69,12.19,4.18,19.54,4.18s14.3-1.49,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S442.12,228.22,436.57,225.37Z"
                                                style="opacity: 0.4; transform-origin: 417.02px 236.375px;"
                                                class="animable" id="elbe8buokhmx7"></path>
                                        </g>
                                        <path
                                            d="M446.12,220.63c-7.74-4.47-18.08-6.93-29.1-6.93s-21.34,2.46-29.08,6.93c-7.43,4.29-11.51,9.87-11.51,15.73s4.08,11.45,11.51,15.74C395.68,256.56,406,259,417,259s21.36-2.46,29.1-6.92c7.42-4.29,11.51-9.88,11.51-15.74S453.54,224.92,446.12,220.63Zm-9.55,26.72c-5.25,2.69-12.18,4.18-19.55,4.18s-14.3-1.49-19.54-4.18c-5.56-2.85-8.62-6.75-8.62-11s3.06-8.14,8.62-11c5.24-2.68,12.19-4.17,19.54-4.17s14.3,1.49,19.55,4.17c5.55,2.85,8.61,6.76,8.61,11S442.12,244.5,436.57,247.35Z"
                                            style="fill: #314A48; transform-origin: 417.02px 236.35px;"
                                            id="elrju59sp061e" class="animable"></path>
                                        <g id="elen8pccw9zkc">
                                            <path
                                                d="M446.12,220.63c-7.74-4.47-18.08-6.93-29.1-6.93s-21.34,2.46-29.08,6.93c-7.43,4.29-11.51,9.87-11.51,15.73s4.08,11.45,11.51,15.74C395.68,256.56,406,259,417,259s21.36-2.46,29.1-6.92c7.42-4.29,11.51-9.88,11.51-15.74S453.54,224.92,446.12,220.63Zm-9.55,26.72c-5.25,2.69-12.18,4.18-19.55,4.18s-14.3-1.49-19.54-4.18c-5.56-2.85-8.62-6.75-8.62-11s3.06-8.14,8.62-11c5.24-2.68,12.19-4.17,19.54-4.17s14.3,1.49,19.55,4.17c5.55,2.85,8.61,6.76,8.61,11S442.12,244.5,436.57,247.35Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 417.02px 236.35px;"
                                                class="animable" id="elzoqjzh6ssd"></path>
                                        </g>
                                        <g id="elxrewvkyvavd">
                                            <path
                                                d="M435.9,246.05c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.75,0-10.43,14,0,19.38S425.48,251.4,435.9,246.05Z"
                                                style="opacity: 0.2; transform-origin: 417.027px 236.36px;"
                                                class="animable" id="eliyt1plnlkic"></path>
                                        </g>
                                        <path
                                            d="M435.9,246.05c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.75,0-10.43,14,0,19.38S425.48,251.4,435.9,246.05Z"
                                            style="fill: #314A48; transform-origin: 417.027px 236.36px;"
                                            id="elm6bfizws16" class="animable"></path>
                                        <g id="el0kj6an7sehw">
                                            <path
                                                d="M435.9,246.05c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.75,0-10.43,14,0,19.38S425.48,251.4,435.9,246.05Z"
                                                style="opacity: 0.45; transform-origin: 417.027px 236.36px;"
                                                class="animable" id="elzc5znu2h81q"></path>
                                        </g>
                                        <path
                                            d="M435.9,230.3c-10.42-5.35-27.33-5.35-37.75,0-4.31,2.21-6.84,5-7.58,7.87.74,2.88,3.27,5.67,7.58,7.88,10.42,5.35,27.33,5.35,37.75,0,4.32-2.21,6.84-5,7.59-7.87C442.74,235.3,440.22,232.51,435.9,230.3Z"
                                            style="fill: #314A48; transform-origin: 417.03px 238.175px;"
                                            id="eln40q1abyl7" class="animable"></path>
                                        <g id="elwtv7mivx81g">
                                            <path
                                                d="M435.9,230.3c-10.42-5.35-27.33-5.35-37.75,0-4.31,2.21-6.84,5-7.58,7.87.74,2.88,3.27,5.67,7.58,7.88,10.42,5.35,27.33,5.35,37.75,0,4.32-2.21,6.84-5,7.59-7.87C442.74,235.3,440.22,232.51,435.9,230.3Z"
                                                style="opacity: 0.3; transform-origin: 417.03px 238.175px;"
                                                class="animable" id="elnaezpf0mwfq"></path>
                                        </g>
                                        <polygon
                                            points="419.55 226.88 419.55 230.79 418.22 232.21 415.75 234.79 415.75 230.72 419.55 226.88"
                                            style="fill: #314A48; transform-origin: 417.65px 230.835px;"
                                            id="elf7f80c42u19" class="animable"></polygon>
                                        <g id="els4zqzbhkvqs">
                                            <polygon
                                                points="419.55 226.88 419.55 230.79 418.22 232.21 415.75 234.79 415.75 230.72 419.55 226.88"
                                                style="opacity: 0.3; transform-origin: 417.65px 230.835px;"
                                                class="animable" id="elxh16kh95lz"></polygon>
                                        </g>
                                        <path
                                            d="M415.75,230.72V233a22.58,22.58,0,0,1-3.8,1.08,3.53,3.53,0,0,1-2.41-.38c-.94-.57-1.09-1.4.7-2.44A6.27,6.27,0,0,1,415.75,230.72Z"
                                            style="fill: #314A48; transform-origin: 412.303px 232.233px;"
                                            id="elp9nqoyt266" class="animable"></path>
                                        <g id="elpp4iuc1cdr">
                                            <path
                                                d="M415.75,230.72V233a22.58,22.58,0,0,1-3.8,1.08,3.53,3.53,0,0,1-2.41-.38c-.94-.57-1.09-1.4.7-2.44A6.27,6.27,0,0,1,415.75,230.72Z"
                                                style="opacity: 0.45; transform-origin: 412.303px 232.233px;"
                                                class="animable" id="elusu14l4rvs"></path>
                                        </g>
                                        <path
                                            d="M433.47,241.84v3.37l-4.28,2.48L426,245.85a16.15,16.15,0,0,1-12.26.56V243l1.94-1.93-1.94.54a12.56,12.56,0,0,1-9.56-1.09c-1.92-1.11-2.81-2.32-2.8-3.57v-3.29l.34-1.44-1.16-.68v-3.38l3.16,1.83c-1.54,1.2-2.36,2.44-2.34,3.63v0c0,1.22.93,2.4,2.8,3.48,4.17,2.4,8.29,1.61,12.07.4,3.52-1.15,6.08-2.5,8.11-1.33,1.25.73.87,1.61-.54,2.43a7,7,0,0,1-6,.35l-4,4a13.45,13.45,0,0,0,2.23.66,16.61,16.61,0,0,0,10-1.21l3.17,1.84Z"
                                            style="fill: #314A48; transform-origin: 417.015px 237.925px;"
                                            id="el5c5ii1v9oxt" class="animable"></path>
                                        <g id="el6i455s9ncdi">
                                            <path
                                                d="M401.4,233.64v0c0,1.22.93,2.4,2.8,3.48,4.17,2.4,8.29,1.61,12.07.4,3.52-1.15,6.08-2.5,8.11-1.33,1.25.73.87,1.61-.54,2.43a7,7,0,0,1-6,.35l-4,4a13.45,13.45,0,0,0,2.23.66,16.61,16.61,0,0,0,10-1.21l3.17,1.84,4.28-2.47v3.37l-4.28,2.48L426,245.85a16.15,16.15,0,0,1-12.26.56V243l1.94-1.93-1.94.54a12.56,12.56,0,0,1-9.56-1.09c-1.92-1.11-2.81-2.32-2.8-3.57v-3.29l.34-1.44"
                                                style="opacity: 0.2; transform-origin: 417.45px 239.93px;"
                                                class="animable" id="elgh910s2zqie"></path>
                                        </g>
                                        <g id="elo0fca5jfdcr">
                                            <path d="M400.58,228.18v3.38l1.16.68h0a7,7,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 402.16px 230.21px;"
                                                class="animable" id="elx1tp1z45axd"></path>
                                        </g>
                                        <path
                                            d="M432.76,239.63a3.91,3.91,0,0,1-.32,1.61L430.29,240a4.25,4.25,0,0,0,2.44-3.26C432.74,237.68,432.76,239.53,432.76,239.63Z"
                                            style="fill: #314A48; transform-origin: 431.525px 238.99px;"
                                            id="el6jt30q7ei7p" class="animable"></path>
                                        <g id="eltmgzk60dqtk">
                                            <g style="opacity: 0.3; transform-origin: 431.525px 238.99px;"
                                                class="animable" id="eljento3os4y">
                                                <path
                                                    d="M432.76,239.63a3.91,3.91,0,0,1-.32,1.61L430.29,240a4.25,4.25,0,0,0,2.44-3.26C432.74,237.68,432.76,239.53,432.76,239.63Z"
                                                    id="elb1mp610twai" class="animable"
                                                    style="transform-origin: 431.525px 238.99px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M432.75,241.42,430.3,240a4.16,4.16,0,0,0,2.45-3.75c0-.12,0-.22,0-.33-.11-1.17-1-2.3-2.74-3.32h0a11.27,11.27,0,0,0-5.69-1.49h-.55l-.5,0-.45,0-.18,0-.34,0a24.24,24.24,0,0,0-2.77.57c-.43.12-.87.25-1.29.39l-1.8.58-.67.23a20,20,0,0,1-3.8,1.08,3.86,3.86,0,0,1-1.64-.06,3.15,3.15,0,0,1-.76-.32c-.95-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.57l3.8-3.84a16.24,16.24,0,0,0-11.48.64l-3.18-1.83-4.31,2.5,3.17,1.82c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.81,3.48,4.17,2.41,8.3,1.61,12.08.39,3.51-1.14,6.06-2.49,8.09-1.32,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.05,4a15.22,15.22,0,0,0,2.24.65,14.93,14.93,0,0,0,3.91.29,18.22,18.22,0,0,0,6.11-1.49l3.18,1.83,4.27-2.47Z"
                                            style="fill: #314A48; transform-origin: 417.06px 234.835px;"
                                            id="eld79cnh2rejs" class="animable"></path>
                                        <g id="elhwyo1mi9yd">
                                            <g style="opacity: 0.3; transform-origin: 417.06px 234.835px;"
                                                class="animable" id="elc017r8xdx5">
                                                <path
                                                    d="M432.75,241.42,430.3,240a4.16,4.16,0,0,0,2.45-3.75c0-.12,0-.22,0-.33-.11-1.17-1-2.3-2.74-3.32h0a11.27,11.27,0,0,0-5.69-1.49h-.55l-.5,0-.45,0-.18,0-.34,0a24.24,24.24,0,0,0-2.77.57c-.43.12-.87.25-1.29.39l-1.8.58-.67.23a20,20,0,0,1-3.8,1.08,3.86,3.86,0,0,1-1.64-.06,3.15,3.15,0,0,1-.76-.32c-.95-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.57l3.8-3.84a16.24,16.24,0,0,0-11.48.64l-3.18-1.83-4.31,2.5,3.17,1.82c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.81,3.48,4.17,2.41,8.3,1.61,12.08.39,3.51-1.14,6.06-2.49,8.09-1.32,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.05,4a15.22,15.22,0,0,0,2.24.65,14.93,14.93,0,0,0,3.91.29,18.22,18.22,0,0,0,6.11-1.49l3.18,1.83,4.27-2.47Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 417.06px 234.835px;"
                                                    id="elwjm2xnymexh" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="426.02 242.47 426.02 245.85 429.19 247.69 429.19 244.31 426.02 242.47"
                                            style="fill: #314A48; transform-origin: 427.605px 245.08px;"
                                            id="elmg890mqhseo" class="animable"></polygon>
                                        <g id="el9q05gjgyhl8">
                                            <polygon
                                                points="426.02 242.47 426.02 245.85 429.19 247.69 429.19 244.31 426.02 242.47"
                                                style="opacity: 0.45; transform-origin: 427.605px 245.08px;"
                                                class="animable" id="elwys6snnk3et"></polygon>
                                        </g>
                                        <g id="elglvph2hx88n">
                                            <polygon
                                                points="400.58 228.19 404.89 225.69 408.06 227.52 404.92 226.2 400.58 228.19"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 404.32px 226.94px;"
                                                class="animable" id="el87j63bquyx9"></polygon>
                                        </g>
                                        <g id="elxbor6x8m6t">
                                            <path
                                                d="M410.24,231.29a6.27,6.27,0,0,1,5.51-.57l3.8-3.84-3.8,3.44A5.81,5.81,0,0,0,410.24,231.29Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 414.895px 229.085px;"
                                                class="animable" id="elp07j4ym4le"></path>
                                        </g>
                                        <g id="elowizbajc5r">
                                            <path
                                                d="M413.75,243a15.22,15.22,0,0,0,2.24.65,14.93,14.93,0,0,0,3.91.29,13.8,13.8,0,0,1-5.51-1.1L417.8,239Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 416.825px 241.482px;"
                                                class="animable" id="elo7h4owpoxyl"></path>
                                        </g>
                                        <g id="eluae3rjao0tn">
                                            <polygon
                                                points="426.02 242.47 429.19 243.91 433.46 241.84 429.19 244.31 426.02 242.47"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 429.74px 243.075px;"
                                                class="animable" id="el7ydwg4kwson"></polygon>
                                        </g>
                                        <g id="el66rx3frecq7">
                                            <path
                                                d="M388.26,221.2c11.24-6.82,28.76-7.5,28.76-7.5-11,0-21.34,2.46-29.08,6.93-7.43,4.29-11.51,9.87-11.51,15.73C376.43,236.36,376.83,228.15,388.26,221.2Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 396.725px 225.03px;"
                                                class="animable" id="eliastlsdbars"></path>
                                        </g>
                                        <g id="elbbfwpb65w8s">
                                            <path
                                                d="M413.75,260.8c11.93.53,24.17-1.84,33.29-7.11,9.49-5.48,13.55-12.9,12.18-20,0,0,1.77,11.64-12.89,19.6S413.75,260.8,413.75,260.8Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 436.615px 247.281px;"
                                                class="animable" id="elgs9bf8eof2m"></path>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--Arrow--inject-15" class="animable animator-active"
                                    style="transform-origin: 215.315px 150.58px;">
                                    <g id="freepik--arrow--inject-15" class="animable"
                                        style="transform-origin: 215.315px 150.58px;">
                                        <polygon
                                            points="338.94 48.07 334.65 45.57 297.39 58.97 305.62 66.67 200.22 179.22 178.26 158.49 173.96 155.99 91.69 243.84 101.56 253.08 105.86 255.59 177.48 179.11 197.94 197.89 204.38 201.65 318.96 79.3 323.71 83.61 328.01 86.12 338.94 48.07"
                                            style="fill: #314A48; transform-origin: 215.315px 150.58px;"
                                            id="elmc1uhc9sm8" class="animable"></polygon>
                                        <g id="elh87mpzzlkvo">
                                            <polygon
                                                points="338.94 48.07 334.65 45.57 297.39 58.97 305.62 66.67 200.22 179.22 178.26 158.49 173.96 155.99 91.69 243.84 101.56 253.08 105.86 255.59 177.48 179.11 197.94 197.89 204.38 201.65 318.96 79.3 323.71 83.61 328.01 86.12 338.94 48.07"
                                                style="fill: rgb(255, 255, 255); opacity: 0.25; transform-origin: 215.315px 150.58px;"
                                                class="animable" id="el3o0cqzgexjs"></polygon>
                                        </g>
                                        <polygon
                                            points="338.94 48.07 328.01 86.12 319.78 78.42 204.38 201.65 178.88 177.61 105.86 255.59 95.98 246.34 178.26 158.49 203.75 182.53 309.91 69.17 301.69 61.47 338.94 48.07"
                                            style="fill: #314A48; transform-origin: 217.46px 151.83px;"
                                            id="eli1kdrmqnmh" class="animable"></polygon>
                                        <polygon
                                            points="178.88 177.61 177.48 179.11 197.94 197.89 204.38 201.65 178.88 177.61"
                                            style="fill: #314A48; transform-origin: 190.93px 189.63px;"
                                            id="el4a8h88y85x8" class="animable"></polygon>
                                        <g id="elnsc5dj4v36">
                                            <polygon
                                                points="178.88 177.61 177.48 179.11 197.94 197.89 204.38 201.65 178.88 177.61"
                                                style="opacity: 0.2; transform-origin: 190.93px 189.63px;"
                                                class="animable" id="elvmzpenzk7l"></polygon>
                                        </g>
                                        <polygon
                                            points="305.62 66.67 309.91 69.17 301.69 61.47 297.39 58.97 305.62 66.67"
                                            style="fill: #314A48; transform-origin: 303.65px 64.07px;" id="eloelnp4i8jk"
                                            class="animable"></polygon>
                                        <g id="el7wsfxllb52g">
                                            <polygon
                                                points="305.62 66.67 309.91 69.17 301.69 61.47 297.39 58.97 305.62 66.67"
                                                style="opacity: 0.2; transform-origin: 303.65px 64.07px;"
                                                class="animable" id="el74n1p0gnqoc"></polygon>
                                        </g>
                                        <polygon points="318.96 79.3 319.78 78.42 328.01 86.12 323.71 83.61 318.96 79.3"
                                            style="fill: #314A48; transform-origin: 323.485px 82.27px;"
                                            id="elojaql75d5f" class="animable"></polygon>
                                        <g id="ely0e1085rjr">
                                            <polygon
                                                points="318.96 79.3 319.78 78.42 328.01 86.12 323.71 83.61 318.96 79.3"
                                                style="opacity: 0.2; transform-origin: 323.485px 82.27px;"
                                                class="animable" id="elux3iur4asg8"></polygon>
                                        </g>
                                        <polygon
                                            points="91.69 243.84 95.98 246.34 105.86 255.59 101.56 253.08 91.69 243.84"
                                            style="fill: #314A48; transform-origin: 98.775px 249.715px;"
                                            id="elyrrwabsn9fn" class="animable"></polygon>
                                        <g id="elfu63iwl5a9">
                                            <polygon
                                                points="91.69 243.84 95.98 246.34 105.86 255.59 101.56 253.08 91.69 243.84"
                                                style="opacity: 0.2; transform-origin: 98.775px 249.715px;"
                                                class="animable" id="ellvdptrrzwcd"></polygon>
                                        </g>
                                        <g id="elx5ie9ucojfd">
                                            <polygon
                                                points="123.1 217.39 178.26 160.31 203.75 182.53 178.26 158.49 123.1 217.39"
                                                style="fill: rgb(255, 255, 255); opacity: 0.5; transform-origin: 163.425px 187.94px;"
                                                class="animable" id="el5rwayzcgeft"></polygon>
                                        </g>
                                        <g id="el98rgkvf8x4">
                                            <polygon
                                                points="252.39 130.6 311.16 69.17 303.24 61.85 338.94 48.07 301.69 61.47 309.91 69.17 252.39 130.6"
                                                style="fill: rgb(255, 255, 255); opacity: 0.5; transform-origin: 295.665px 89.335px;"
                                                class="animable" id="el5p5wtbx6t76"></polygon>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--bar-chart--inject-15" class="animable"
                                    style="transform-origin: 266.22px 259.585px;">
                                    <g id="freepik--bar-graph--inject-15" class="animable"
                                        style="transform-origin: 266.22px 259.585px;">
                                        <g id="el0ce28qx8q0v">
                                            <g style="opacity: 0.3; transform-origin: 346.27px 292.37px;"
                                                class="animable" id="elr03fag7j4w8">
                                                <path
                                                    d="M340.94,320.78a.63.63,0,0,1-.33-.09L297.91,296a.68.68,0,0,1-.33-.57.66.66,0,0,1,.33-.57l53.37-30.81a.65.65,0,0,1,.66,0l42.7,24.65a.65.65,0,0,1,.32.57.67.67,0,0,1-.32.57l-53.37,30.81A.68.68,0,0,1,340.94,320.78Zm-41.38-25.31,41.38,23.89L393,289.31l-41.38-23.89Z"
                                                    style="fill: #314A48; transform-origin: 346.27px 292.37px;"
                                                    id="eleee96lw44ua" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="340.94 313.96 340.93 149.09 308.91 130.6 308.92 295.47 340.94 313.96"
                                            style="fill: #314A48; transform-origin: 324.925px 222.28px;"
                                            id="elz608hblpsd" class="animable"></polygon>
                                        <g id="el950uijwlr9">
                                            <polygon
                                                points="340.94 313.96 340.93 149.09 308.91 130.6 308.92 295.47 340.94 313.96"
                                                style="opacity: 0.2; transform-origin: 324.925px 222.28px;"
                                                class="animable" id="elr25z74t40r9"></polygon>
                                        </g>
                                        <polygon
                                            points="340.94 313.96 383.63 289.31 383.64 124.44 340.93 149.09 340.94 313.96"
                                            style="fill: #314A48; transform-origin: 362.285px 219.2px;"
                                            id="el865qa4wfi7u" class="animable"></polygon>
                                        <polygon
                                            points="383.64 124.44 351.62 105.95 308.91 130.6 340.93 149.09 383.64 124.44"
                                            style="fill: #314A48; transform-origin: 346.275px 127.52px;"
                                            id="el4kqmobafl9a" class="animable"></polygon>
                                        <g id="elpxvgealsyhd">
                                            <polygon
                                                points="383.64 124.44 351.62 105.95 308.91 130.6 340.93 149.09 383.64 124.44"
                                                style="fill: rgb(255, 255, 255); opacity: 0.25; transform-origin: 346.275px 127.52px;"
                                                class="animable" id="elqycyjw4xsb"></polygon>
                                        </g>
                                        <g id="elfxbcm3lw5s5">
                                            <path
                                                d="M383.64,124.44l-43.13,23.78-14-7.82-17.59-9.8,17.28,10.34,14.17,8.46c-.34,4.8-.43,87.56-.37,92.37s.31,67.28.95,72.19c.64-4.91.86-67.28.93-72.19.07-4.65,0-87.26-.34-91.91Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 346.28px 219.2px;"
                                                class="animable" id="el86qmvu2p0lb"></path>
                                        </g>
                                        <g id="elkj03ag9jba">
                                            <g style="opacity: 0.3; transform-origin: 266.22px 338.612px;"
                                                class="animable" id="elh19g0qhl88f">
                                                <path
                                                    d="M260.88,367a.68.68,0,0,1-.33-.09l-42.69-24.65a.68.68,0,0,1-.33-.57.66.66,0,0,1,.33-.57l53.37-30.81a.65.65,0,0,1,.66,0L314.58,335a.66.66,0,0,1,.33.57.68.68,0,0,1-.33.57l-53.37,30.81A.63.63,0,0,1,260.88,367ZM219.5,341.69l41.38,23.89,52.06-30.05-41.38-23.89Z"
                                                    style="fill: #314A48; transform-origin: 266.22px 338.612px;"
                                                    id="elbn0qfl9rn6" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="260.88 360.18 260.88 227.75 228.86 209.26 228.86 341.69 260.88 360.18"
                                            style="fill: #314A48; transform-origin: 244.87px 284.72px;"
                                            id="eluny8ankmvaq" class="animable"></polygon>
                                        <g id="elxq8ecyb6ry">
                                            <polygon
                                                points="260.88 360.18 260.88 227.75 228.86 209.26 228.86 341.69 260.88 360.18"
                                                style="fill: rgb(255, 255, 255); opacity: 0.1; transform-origin: 244.87px 284.72px;"
                                                class="animable" id="elgha828ujifo"></polygon>
                                        </g>
                                        <polygon
                                            points="260.88 360.18 303.58 335.53 303.58 203.1 260.88 227.75 260.88 360.18"
                                            style="fill: #314A48; transform-origin: 282.23px 281.64px;"
                                            id="el8w1jf9qwxsa" class="animable"></polygon>
                                        <g id="elc9cruhmdgau">
                                            <polygon
                                                points="260.88 360.18 303.58 335.53 303.58 203.1 260.88 227.75 260.88 360.18"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 282.23px 281.64px;"
                                                class="animable" id="elrdmdvf6o2g"></polygon>
                                        </g>
                                        <polygon
                                            points="303.58 203.1 271.56 184.62 228.86 209.26 260.88 227.75 303.58 203.1"
                                            style="fill: #314A48; transform-origin: 266.22px 206.185px;"
                                            id="elh36a1516o84" class="animable"></polygon>
                                        <g id="elfjny2256u3d">
                                            <polygon
                                                points="303.58 203.1 271.56 184.62 228.86 209.26 260.88 227.75 303.58 203.1"
                                                style="fill: rgb(255, 255, 255); opacity: 0.55; transform-origin: 266.22px 206.185px;"
                                                class="animable" id="elx30k0hknqwq"></polygon>
                                        </g>
                                        <g id="el98rg5l2948">
                                            <path
                                                d="M261.45,228.08l42.13-25-42.7,24c-4.65-2.64-9.76-5.47-14.43-8.07l-17.59-9.79,17.28,10.33c4.72,2.83,9.44,5.67,14.18,8.46-.15,5,.24,127,.56,132.12C261.21,355.09,261.59,232.9,261.45,228.08Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 266.22px 281.605px;"
                                                class="animable" id="elstnyosw0w4m"></path>
                                        </g>
                                        <g id="elizh0kzcmhl">
                                            <g style="opacity: 0.3; transform-origin: 186.165px 384.83px;"
                                                class="animable" id="elhp70k8favw9">
                                                <path
                                                    d="M180.83,413.22a.66.66,0,0,1-.33-.09l-42.7-24.65a.67.67,0,0,1-.32-.57.65.65,0,0,1,.32-.57l53.37-30.81a.65.65,0,0,1,.66,0l42.7,24.65a.65.65,0,0,1,.32.57.67.67,0,0,1-.32.57l-53.37,30.81A.68.68,0,0,1,180.83,413.22Zm-41.38-25.31,41.38,23.89,52-30.05L191.5,357.86Z"
                                                    style="fill: #314A48; transform-origin: 186.165px 384.83px;"
                                                    id="ele4ykhkxlpua" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="180.83 406.4 180.82 317.24 148.8 298.75 148.81 387.91 180.83 406.4"
                                            style="fill: #314A48; transform-origin: 164.815px 352.575px;"
                                            id="elr3456idsvmj" class="animable"></polygon>
                                        <g id="elkwm55ndr9de">
                                            <polygon
                                                points="180.83 406.4 180.82 317.24 148.8 298.75 148.81 387.91 180.83 406.4"
                                                style="opacity: 0.45; transform-origin: 164.815px 352.575px;"
                                                class="animable" id="elztqxiuvo8q"></polygon>
                                        </g>
                                        <polygon
                                            points="180.83 406.4 223.52 381.75 223.53 292.59 180.82 317.24 180.83 406.4"
                                            style="fill: #314A48; transform-origin: 202.175px 349.495px;"
                                            id="elmt1zr2ur8ei" class="animable"></polygon>
                                        <g id="el9ynpjrtsiq8">
                                            <polygon
                                                points="180.83 406.4 223.52 381.75 223.53 292.59 180.82 317.24 180.83 406.4"
                                                style="opacity: 0.3; transform-origin: 202.175px 349.495px;"
                                                class="animable" id="el79llixu6bv5"></polygon>
                                        </g>
                                        <polygon
                                            points="223.53 292.59 191.51 274.1 148.8 298.75 180.82 317.24 223.53 292.59"
                                            style="fill: #314A48; transform-origin: 186.165px 295.67px;"
                                            id="elq2v0u0sh53" class="animable"></polygon>
                                        <g id="elzskbj5nps08">
                                            <polygon
                                                points="223.53 292.59 191.51 274.1 148.8 298.75 180.82 317.24 223.53 292.59"
                                                style="opacity: 0.15; transform-origin: 186.165px 295.67px;"
                                                class="animable" id="elj6cjct0qz2"></polygon>
                                        </g>
                                        <g id="elv9mbo5iedum">
                                            <path
                                                d="M223.53,292.59l-42.71,24-14.43-8.05-17.59-9.8,17.28,10.34L180.24,318s-.05,85.4.59,88.43c.64-3,.62-88.41.62-88.41Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 186.165px 349.51px;"
                                                class="animable" id="elybj02gx5tvm"></path>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--pie-charts--inject-15" class="animable"
                                    style="transform-origin: 343.31px 375.9px;">
                                    <g id="freepik--pie-chart--inject-15" class="animable"
                                        style="transform-origin: 272.75px 416.632px;">
                                        <path
                                            d="M272.75,443.13c-10.87,0-21.74-2.39-30-7.17-8.13-4.69-12.6-11-12.6-17.65s4.47-13,12.6-17.65c16.56-9.56,43.5-9.56,60,0h0c8.13,4.69,12.6,11,12.6,17.65s-4.47,13-12.6,17.65S283.63,443.13,272.75,443.13Zm0-48.56c-10.68,0-21.36,2.34-29.49,7-7.77,4.49-12.05,10.42-12.05,16.71s4.28,12.22,12.05,16.71c16.26,9.38,42.71,9.38,59,0,7.77-4.49,12.05-10.42,12.05-16.71S310,406.09,302.24,401.6h0C294.11,396.91,283.43,394.57,272.76,394.57Z"
                                            style="fill: #314A48; transform-origin: 272.75px 418.31px;"
                                            id="el983x4n74mbr" class="animable"></path>
                                        <g id="freepik--pie-chart--inject-15" class="animable"
                                            style="transform-origin: 272.737px 414.488px;">
                                            <path d="M273.88,409.43v7.08l-7-19v-7.07h0Z"
                                                style="fill: rgb(38, 50, 56); transform-origin: 270.38px 403.475px;"
                                                id="elovjraamzr1l" class="animable"></path>
                                            <polygon
                                                points="273.88 409.43 273.88 416.51 302.73 406.5 302.73 399.43 273.88 409.43"
                                                style="fill: rgb(55, 71, 79); transform-origin: 288.305px 407.97px;"
                                                id="elowmanwuw88h" class="animable"></polygon>
                                            <path
                                                d="M266.89,390.43l7,19,28.85-10C294.81,392.52,281,389.06,266.89,390.43Z"
                                                style="fill: rgb(69, 90, 100); transform-origin: 284.815px 399.782px;"
                                                id="el4596zr31x6w" class="animable"></path>
                                            <path d="M236.75,410.75a1.48,1.48,0,0,0,0,.21v-.21Z"
                                                style="fill: #314A48; transform-origin: 236.748px 410.855px;"
                                                id="elln8ooigv8e8" class="animable"></path>
                                            <path
                                                d="M271,418.36l-4.29,20.15c-17.48-1.71-30.11-10.55-30-20.66V411c0,10,12.61,18.78,30,20.48l2.95-13.85Z"
                                                style="fill: #314A48; transform-origin: 253.855px 424.755px;"
                                                id="eljvt64lvf6o" class="animable"></path>
                                            <g id="ely6dpv3hmyv">
                                                <path
                                                    d="M271,418.36l-4.29,20.15c-17.48-1.71-30.11-10.55-30-20.66V411c0,10,12.61,18.78,30,20.48l2.95-13.85Z"
                                                    style="opacity: 0.4; transform-origin: 253.855px 424.755px;"
                                                    class="animable" id="el4vrs7dbzar9"></path>
                                            </g>
                                            <path
                                                d="M270,431.67v7.08a55.9,55.9,0,0,0,21.28-2.91c8.25-2.86,14.07-7.41,16.4-12.79a13,13,0,0,0,1.08-4.94c0-.67,0-7.32,0-7.32s-2-.15-4.34-2.65L299,410l-24.74,8.58Z"
                                                style="fill: #314A48; transform-origin: 289.38px 423.491px;"
                                                id="elqhers16ulc" class="animable"></path>
                                            <g id="elmabn54f2dg">
                                                <path
                                                    d="M270,431.67v7.08a55.9,55.9,0,0,0,21.28-2.91c8.25-2.86,14.07-7.41,16.4-12.79a13,13,0,0,0,1.08-4.94c0-.67,0-7.32,0-7.32s-2-.15-4.34-2.65L299,410l-24.74,8.58Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.6; transform-origin: 289.38px 423.491px;"
                                                    class="animable" id="elv1z2drs1xs"></path>
                                            </g>
                                            <polygon
                                                points="248.48 402.66 269.97 415.06 269.97 407.99 248.48 395.58 248.48 402.66"
                                                style="fill: #314A48; transform-origin: 259.225px 405.32px;"
                                                id="elgyb3z58lmn" class="animable"></polygon>
                                            <g id="el6p3kdmsdddh">
                                                <polygon
                                                    points="248.48 402.66 269.97 415.06 269.97 407.99 248.48 395.58 248.48 402.66"
                                                    style="opacity: 0.1; transform-origin: 259.225px 405.32px;"
                                                    class="animable" id="elhf2dwkxjqqu"></polygon>
                                            </g>
                                            <path
                                                d="M237,408.41c-2.35,11.06,10.87,21.19,29.71,23L271,411.29l-24.87-14.36C241,400.18,238,404,237,408.41Z"
                                                style="fill: #314A48; transform-origin: 253.865px 414.17px;"
                                                id="elaqgoxces7uo" class="animable"></path>
                                            <g id="el1vov152gn08">
                                                <path
                                                    d="M237,408.41c-2.35,11.06,10.87,21.19,29.71,23L271,411.29l-24.87-14.36C241,400.18,238,404,237,408.41Z"
                                                    style="opacity: 0.3; transform-origin: 253.865px 414.17px;"
                                                    class="animable" id="elgm2odvvjhaa"></path>
                                            </g>
                                            <path
                                                d="M270.13,431.08l-.12.59a55.71,55.71,0,0,0,21.28-2.91c8.25-2.86,14.07-7.4,16.4-12.79,2.19-5.08,1-10.33-3.26-14.9l-5.4,1.87-24.74,8.58Z"
                                                style="fill: #314A48; transform-origin: 289.388px 416.417px;"
                                                id="elfc4op5mz544" class="animable"></path>
                                            <g id="el9dorfo1xlql">
                                                <path
                                                    d="M270.13,431.08l-.12.59a55.71,55.71,0,0,0,21.28-2.91c8.25-2.86,14.07-7.4,16.4-12.79,2.19-5.08,1-10.33-3.26-14.9l-5.4,1.87-24.74,8.58Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.75; transform-origin: 289.388px 416.417px;"
                                                    class="animable" id="el9ok9d06lqqq"></path>
                                            </g>
                                            <path
                                                d="M248.48,395.58,270,408l-6.32-17.17A44.25,44.25,0,0,0,248.48,395.58Z"
                                                style="fill: #314A48; transform-origin: 259.24px 399.415px;"
                                                id="elxoiiilm93x" class="animable"></path>
                                            <polygon
                                                points="271.01 418.36 271.01 411.29 266.72 431.44 266.72 438.51 271.01 418.36"
                                                style="fill: #314A48; transform-origin: 268.865px 424.9px;"
                                                id="el8rcuvqqkuxo" class="animable"></polygon>
                                            <g id="eldwjrt1sqzpp">
                                                <polygon
                                                    points="271.01 418.36 271.01 411.29 266.72 431.44 266.72 438.51 271.01 418.36"
                                                    style="opacity: 0.55; transform-origin: 268.865px 424.9px;"
                                                    class="animable" id="el86xkind67bl"></polygon>
                                            </g>
                                        </g>
                                    </g>
                                    <g id="freepik--pie-chart--inject-15" class="animable"
                                        style="transform-origin: 343.345px 375.899px;">
                                        <path
                                            d="M343.32,402.4c-10.88,0-21.75-2.39-30-7.17s-12.6-11-12.6-17.66,4.47-13,12.6-17.65c16.56-9.56,43.5-9.56,60.06,0h0c8.12,4.69,12.59,11,12.59,17.65s-4.47,13-12.6,17.66S354.19,402.4,343.32,402.4Zm0-48.57c-10.68,0-21.36,2.35-29.48,7-7.78,4.49-12.06,10.42-12.06,16.7s4.28,12.22,12.06,16.71c16.25,9.38,42.7,9.38,59,0,7.77-4.49,12.05-10.42,12.05-16.71s-4.28-12.21-12.05-16.7h0C364.67,356.18,354,353.83,343.32,353.83Z"
                                            style="fill: #314A48; transform-origin: 343.345px 377.575px;"
                                            id="el6f261a9w3si" class="animable"></path>
                                        <g id="freepik--pie-chart--inject-15" class="animable"
                                            style="transform-origin: 343.319px 373.699px;">
                                            <path
                                                d="M378,371.61v-7.06c-13.62-7.87-44.75-10.1-58.83-2.68l24.73,14.29,26.44-3.53Z"
                                                style="fill: #314A48; transform-origin: 348.585px 366.745px;"
                                                id="elakcokpe8uoe" class="animable"></path>
                                            <g id="ele5swzuaisze">
                                                <path
                                                    d="M378,371.61v-7.06c-13.62-7.87-44.75-10.1-58.83-2.68l24.73,14.29,26.44-3.53Z"
                                                    style="opacity: 0.1; transform-origin: 348.585px 366.745px;"
                                                    class="animable" id="el0hef4srw34yb"></path>
                                            </g>
                                            <path
                                                d="M378,364.55c-1.68-3.57-4.56-6.4-9.2-9.08-13.63-7.87-35.55-8.09-49.63-.66l24.73,14.28,26.44-3.52Z"
                                                style="fill: #314A48; transform-origin: 348.585px 359.244px;"
                                                id="elhw35cdqqbd" class="animable"></path>
                                            <polygon
                                                points="343.89 376.15 343.89 369.09 319.15 354.81 319.15 361.87 343.89 376.15"
                                                style="fill: #314A48; transform-origin: 331.52px 365.48px;"
                                                id="elersd7vvy9wl" class="animable"></polygon>
                                            <g id="elhqsu8gn4zza">
                                                <polygon
                                                    points="343.89 376.15 343.89 369.09 319.15 354.81 319.15 361.87 343.89 376.15"
                                                    style="opacity: 0.2; transform-origin: 331.52px 365.48px;"
                                                    class="animable" id="eln99mkv9o2nj"></polygon>
                                            </g>
                                            <path
                                                d="M351.46,397.42c9.35-1.25,17.3-4.52,22.39-9.22,3.6-3.32,5.46-7.08,5.48-10.9v-7.16l-22.28,6.22-12,1.59,1.21,19.94C348,397.81,349.67,397.66,351.46,397.42Z"
                                                style="fill: rgb(55, 71, 79); transform-origin: 362.19px 384.015px;"
                                                id="eljwbfzdowt4a" class="animable"></path>
                                            <path
                                                d="M351.46,390.36c9.35-1.25,17.3-4.52,22.39-9.23,4.8-4.42,6.5-9.62,4.88-14.72l-21.68,2.88-12,1.6,1.21,19.93C348,390.74,349.67,390.6,351.46,390.36Z"
                                                style="fill: rgb(69, 90, 100); transform-origin: 362.188px 378.615px;"
                                                id="elmr2fiod5a9" class="animable"></path>
                                            <path
                                                d="M311.75,387.06l29-10.05-23.89-13.79c-4.41,2.78-9.51,6.73-9.51,6.73s0,5.73,0,7.25C307.33,380.54,308.79,383.92,311.75,387.06Z"
                                                style="fill: #314A48; transform-origin: 324.05px 375.14px;"
                                                id="elwn6gjokcnc8" class="animable"></path>
                                            <g id="el47ej6czu5qv">
                                                <path
                                                    d="M311.75,387.06l29-10.05-23.89-13.79c-4.41,2.78-9.51,6.73-9.51,6.73s0,5.73,0,7.25C307.33,380.54,308.79,383.92,311.75,387.06Z"
                                                    style="opacity: 0.4; transform-origin: 324.05px 375.14px;"
                                                    class="animable" id="elt83k5yjd4p"></path>
                                            </g>
                                            <path
                                                d="M311.75,380l29-10.05-23.89-13.79C306.32,362.76,304.33,372.13,311.75,380Z"
                                                style="fill: #314A48; transform-origin: 324.029px 368.08px;"
                                                id="el9orqjnssxlt" class="animable"></path>
                                            <g id="elthh4p26b2ds">
                                                <path
                                                    d="M311.75,380l29-10.05-23.89-13.79C306.32,362.76,304.33,372.13,311.75,380Z"
                                                    style="opacity: 0.3; transform-origin: 324.029px 368.08px;"
                                                    class="animable" id="eleqo33g9t8c"></path>
                                            </g>
                                            <polygon
                                                points="311.75 387.06 311.75 380 340.71 369.95 340.71 377.01 311.75 387.06"
                                                style="fill: #314A48; transform-origin: 326.23px 378.505px;"
                                                id="elae643uq0agu" class="animable"></polygon>
                                            <g id="elwtyuek961k">
                                                <polygon
                                                    points="311.75 387.06 311.75 380 340.71 369.95 340.71 377.01 311.75 387.06"
                                                    style="opacity: 0.55; transform-origin: 326.23px 378.505px;"
                                                    class="animable" id="eluuk6u2rbu7r"></polygon>
                                            </g>
                                            <path
                                                d="M343,398v-7.06l-1.16-12-28.37,2.78v7.07C320.16,394.58,330.78,397.91,343,398Z"
                                                style="fill: #314A48; transform-origin: 328.235px 388.47px;"
                                                id="elpn1o26uk9ak" class="animable"></path>
                                            <g id="elimxuaxmatb">
                                                <path
                                                    d="M343,398v-7.06l-1.16-12-28.37,2.78v7.07C320.16,394.58,330.78,397.91,343,398Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.6; transform-origin: 328.235px 388.47px;"
                                                    class="animable" id="elkuvh06wtt8l"></path>
                                            </g>
                                            <path
                                                d="M343,390.89l-1.16-19.1-21.48,7.45-6.89,2.39C320.16,387.52,330.78,390.84,343,390.89Z"
                                                style="fill: #314A48; transform-origin: 328.235px 381.34px;"
                                                id="elghy3ug0yuc6" class="animable"></path>
                                            <g id="elm2lt6lh2xs">
                                                <path
                                                    d="M343,390.89l-1.16-19.1-21.48,7.45-6.89,2.39C320.16,387.52,330.78,390.84,343,390.89Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.75; transform-origin: 328.235px 381.34px;"
                                                    class="animable" id="el2speh5zjpiu"></path>
                                            </g>
                                            <polygon
                                                points="345.07 370.89 345.07 377.95 346.28 397.89 346.28 390.82 345.07 370.89"
                                                style="fill: rgb(38, 50, 56); transform-origin: 345.675px 384.39px;"
                                                id="elwx3bvwktnyf" class="animable"></polygon>
                                        </g>
                                    </g>
                                    <g id="freepik--pie-chart--inject-15" class="animable"
                                        style="transform-origin: 413.87px 335.17px;">
                                        <path
                                            d="M413.87,361.67c-10.87,0-21.74-2.39-30-7.17s-12.6-11-12.6-17.66,4.47-13,12.6-17.65c16.56-9.56,43.5-9.56,60,0h0c8.13,4.69,12.6,11,12.6,17.65s-4.47,13-12.6,17.66S424.75,361.67,413.87,361.67Zm0-48.57c-10.67,0-21.35,2.35-29.48,7-7.77,4.49-12.05,10.42-12.05,16.7s4.28,12.22,12.05,16.71c16.26,9.38,42.71,9.38,59,0,7.77-4.49,12.05-10.42,12.05-16.71s-4.28-12.21-12.05-16.7h0C435.23,315.45,424.55,313.1,413.87,313.1Z"
                                            style="fill: #314A48; transform-origin: 413.87px 336.845px;"
                                            id="ely0jsv53u1tn" class="animable"></path>
                                        <g id="freepik--pie-chart--inject-15" class="animable"
                                            style="transform-origin: 413.855px 332.962px;">
                                            <path
                                                d="M389.7,314.09l22.43,13L411,308.74C402.78,309.09,395.78,310.85,389.7,314.09Z"
                                                style="fill: #314A48; transform-origin: 400.915px 317.915px;"
                                                id="el9n5xj9is7wo" class="animable"></path>
                                            <polygon
                                                points="412.13 327.04 412.13 334.1 389.7 321.15 389.7 314.09 412.13 327.04"
                                                style="fill: #314A48; transform-origin: 400.915px 324.095px;"
                                                id="el5guogjub22o" class="animable"></polygon>
                                            <g id="elh8ifu8co8np">
                                                <polygon
                                                    points="412.13 327.04 412.13 334.1 389.7 321.15 389.7 314.09 412.13 327.04"
                                                    style="opacity: 0.2; transform-origin: 400.915px 324.095px;"
                                                    class="animable" id="elv15amxbbtwd"></polygon>
                                            </g>
                                            <polygon
                                                points="441.2 315.91 415.46 327.52 415.46 334.58 441.2 322.97 441.2 315.91"
                                                style="fill: rgb(55, 71, 79); transform-origin: 428.33px 325.245px;"
                                                id="elli5nym032lj" class="animable"></polygon>
                                            <path d="M441.2,315.91c-6.93-4.72-16.16-7.21-26.88-7.24l.7,11.44.44,7.41Z"
                                                style="fill: rgb(69, 90, 100); transform-origin: 427.76px 318.095px;"
                                                id="elv5sg035beh9" class="animable"></path>
                                            <polygon
                                                points="414.32 308.67 414.32 315.73 415.46 334.58 415.46 327.52 414.32 308.67"
                                                style="fill: rgb(38, 50, 56); transform-origin: 414.89px 321.625px;"
                                                id="el1yuhm5u49czi" class="animable"></polygon>
                                            <path
                                                d="M420.68,341.12l13.15,12.64c.74-.28,1.43-.57,2.22-.93,9.07-4.08,13.81-10.2,13.8-16.37v-7c-1-2.66-3.74-2.66-6.62-5L416.36,336.6l-.27.12Z"
                                                style="fill: #314A48; transform-origin: 432.97px 339.11px;"
                                                id="eld9wv38e3tfh" class="animable"></path>
                                            <g id="elgu1oaxl16s">
                                                <path
                                                    d="M420.68,341.12l13.15,12.64c.74-.28,1.43-.57,2.22-.93,9.07-4.08,13.81-10.2,13.8-16.37v-7c-1-2.66-3.74-2.66-6.62-5L416.36,336.6l-.27.12Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.6; transform-origin: 432.97px 339.11px;"
                                                    class="animable" id="elgudiasvnyr9"></path>
                                            </g>
                                            <path
                                                d="M420.68,334.06l13.15,12.64c.74-.28,1.43-.57,2.22-.93,15.18-6.84,18.23-19.38,7.18-28.36l-26.87,12.12-.27.12Z"
                                                style="fill: #314A48; transform-origin: 432.969px 332.055px;"
                                                id="el8ga06skb3ai" class="animable"></path>
                                            <g id="el5fp2jg2t9dd">
                                                <path
                                                    d="M420.68,334.06l13.15,12.64c.74-.28,1.43-.57,2.22-.93,15.18-6.84,18.23-19.38,7.18-28.36l-26.87,12.12-.27.12Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.75; transform-origin: 432.969px 332.055px;"
                                                    class="animable" id="el98b3q7m1p6g"></path>
                                            </g>
                                            <polygon
                                                points="433.83 353.76 433.83 346.7 416.09 329.65 416.09 336.72 433.83 353.76"
                                                style="fill: #314A48; transform-origin: 424.96px 341.705px;"
                                                id="el4n1y8eg9a9c" class="animable"></polygon>
                                            <g id="elk2ipmuggvl">
                                                <polygon
                                                    points="433.83 353.76 433.83 346.7 416.09 329.65 416.09 336.72 433.83 353.76"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.4; transform-origin: 424.96px 341.705px;"
                                                    class="animable" id="elsvn82paxd8f"></polygon>
                                            </g>
                                            <path
                                                d="M387.37,322.5c-3.78,2.38-8,3.88-9.51,6.78v7.07c0,3.68,1.71,7.41,5.26,10.82,4.95,4.75,12.8,8.11,22.11,9.45A59.5,59.5,0,0,0,431,354.74v-7.06l-11.58-4.07-6.8-6.53Z"
                                                style="fill: #314A48; transform-origin: 404.43px 339.877px;"
                                                id="elfb68tg1s53e" class="animable"></path>
                                            <g id="elyxudg2bawo">
                                                <path
                                                    d="M387.37,322.5c-3.78,2.38-8,3.88-9.51,6.78v7.07c0,3.68,1.71,7.41,5.26,10.82,4.95,4.75,12.8,8.11,22.11,9.45A59.5,59.5,0,0,0,431,354.74v-7.06l-11.58-4.07-6.8-6.53Z"
                                                    style="opacity: 0.4; transform-origin: 404.43px 339.877px;"
                                                    class="animable" id="elhby318gt7z"></path>
                                            </g>
                                            <path
                                                d="M387.37,315.44c-10.86,6.84-12.61,16.63-4.25,24.67,4.95,4.75,12.8,8.1,22.11,9.44A59.35,59.35,0,0,0,431,347.68l-11.58-11.13-6.8-6.54Z"
                                                style="fill: #314A48; transform-origin: 404.431px 332.815px;"
                                                id="elh4j4l4h56b" class="animable"></path>
                                            <g id="elmncsxqlzz7">
                                                <path
                                                    d="M387.37,315.44c-10.86,6.84-12.61,16.63-4.25,24.67,4.95,4.75,12.8,8.1,22.11,9.44A59.35,59.35,0,0,0,431,347.68l-11.58-11.13-6.8-6.54Z"
                                                    style="opacity: 0.3; transform-origin: 404.431px 332.815px;"
                                                    class="animable" id="elun8tqclctar"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--coins-1--inject-15" class="animable"
                                    style="transform-origin: 106.85px 378.303px;">
                                    <g id="freepik--coins--inject-15" class="animable"
                                        style="transform-origin: 73.2098px 350.583px;">
                                        <path
                                            d="M113.09,364.63A27.28,27.28,0,0,0,103,355.25c-16.58-9.57-43.45-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38H30.49v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.45,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                            style="fill: #314A48; transform-origin: 72.91px 372.58px;"
                                            id="el9cxcxtorzbu" class="animable"></path>
                                        <g id="el7yz2ndyqhcn">
                                            <g style="opacity: 0.25; transform-origin: 72.91px 372.58px;"
                                                class="animable" id="elw154tyhsymi">
                                                <path
                                                    d="M113.09,364.63A27.28,27.28,0,0,0,103,355.25c-16.58-9.57-43.45-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38H30.49v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.45,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                                    id="elc0v4rnahh9" class="animable"
                                                    style="transform-origin: 72.91px 372.58px;"></path>
                                            </g>
                                        </g>
                                        <g id="elh3b78f4a3d">
                                            <path
                                                d="M72.94,348.07c-10.87,0-21.73,2.39-30,7.18a27.36,27.36,0,0,0-10.14,9.38H30.49v8.67h0c.3,6,4.43,12,12.4,16.61,8.29,4.78,19.15,7.18,30,7.18Z"
                                                style="opacity: 0.1; transform-origin: 51.715px 372.58px;"
                                                class="animable" id="elzo2nq1464o"></path>
                                        </g>
                                        <g id="elnqfruyx1ed7">
                                            <path
                                                d="M49.75,352.05a46,46,0,0,0-6.83,3.2,27.36,27.36,0,0,0-10.14,9.38H30.49v8.67h0c.3,6,4.43,12,12.4,16.61a47,47,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 40.12px 372.58px;"
                                                class="animable" id="el65vzs6sbaq"></path>
                                        </g>
                                        <g id="elrjjse2ir6os">
                                            <path
                                                d="M96.12,352.05a45.54,45.54,0,0,1,6.83,3.2,27.28,27.28,0,0,1,10.14,9.38h2.3v8.67h0c-.31,6-4.47,12-12.44,16.61a46.55,46.55,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 105.755px 372.58px;"
                                                class="animable" id="el28lbrvel42i"></path>
                                        </g>
                                        <path
                                            d="M103,347.3c-16.58-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.57,60,0S119.53,356.87,103,347.3Z"
                                            style="fill: #314A48; transform-origin: 72.9906px 364.63px;"
                                            id="el0z7l5k6okbt" class="animable"></path>
                                        <g id="ely88c135f7ef">
                                            <path
                                                d="M103,347.3c-16.58-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.57,60,0S119.53,356.87,103,347.3Z"
                                                style="opacity: 0.2; transform-origin: 72.9906px 364.63px;"
                                                class="animable" id="elzzzjcpd4pcc"></path>
                                        </g>
                                        <path
                                            d="M92.48,353.64c-5.25-2.69-12.18-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.85-8.62,6.76-8.62,11s3.06,8.14,8.62,11c5.24,2.69,12.19,4.17,19.54,4.17s14.3-1.48,19.55-4.17c5.55-2.85,8.61-6.75,8.61-11S98,356.49,92.48,353.64Z"
                                            style="fill: #314A48; transform-origin: 72.93px 364.64px;"
                                            id="elhn15cn1lajk" class="animable"></path>
                                        <g id="elkq2rdu4f59q">
                                            <path
                                                d="M92.48,353.64c-5.25-2.69-12.18-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.85-8.62,6.76-8.62,11s3.06,8.14,8.62,11c5.24,2.69,12.19,4.17,19.54,4.17s14.3-1.48,19.55-4.17c5.55-2.85,8.61-6.75,8.61-11S98,356.49,92.48,353.64Z"
                                                style="opacity: 0.4; transform-origin: 72.93px 364.64px;"
                                                class="animable" id="elu29rez7j0ra"></path>
                                        </g>
                                        <path
                                            d="M102,348.9C94.29,344.43,84,342,72.93,342s-21.34,2.46-29.08,6.93c-7.43,4.29-11.51,9.87-11.51,15.73s4.08,11.45,11.51,15.73c7.74,4.46,18.06,6.93,29.08,6.93s21.36-2.47,29.1-6.93c7.42-4.28,11.51-9.87,11.51-15.73S109.45,353.19,102,348.9Zm-9.55,26.72c-5.25,2.69-12.18,4.17-19.55,4.17s-14.3-1.48-19.54-4.17c-5.56-2.85-8.62-6.75-8.62-11s3.06-8.14,8.62-11c5.24-2.69,12.19-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.76,8.61,11S98,372.77,92.48,375.62Z"
                                            style="fill: #314A48; transform-origin: 72.94px 364.66px;"
                                            id="elxeeq754j8xg" class="animable"></path>
                                        <g id="el354fu529l67">
                                            <path
                                                d="M102,348.9C94.29,344.43,84,342,72.93,342s-21.34,2.46-29.08,6.93c-7.43,4.29-11.51,9.87-11.51,15.73s4.08,11.45,11.51,15.73c7.74,4.46,18.06,6.93,29.08,6.93s21.36-2.47,29.1-6.93c7.42-4.28,11.51-9.87,11.51-15.73S109.45,353.19,102,348.9Zm-9.55,26.72c-5.25,2.69-12.18,4.17-19.55,4.17s-14.3-1.48-19.54-4.17c-5.56-2.85-8.62-6.75-8.62-11s3.06-8.14,8.62-11c5.24-2.69,12.19-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.76,8.61,11S98,372.77,92.48,375.62Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 72.94px 364.66px;"
                                                class="animable" id="elzgiihfrir4e"></path>
                                        </g>
                                        <g id="elyvesvo06r1">
                                            <path
                                                d="M91.81,374.32c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.75,0-10.43,14,0,19.38S81.39,379.67,91.81,374.32Z"
                                                style="opacity: 0.2; transform-origin: 72.9369px 364.63px;"
                                                class="animable" id="elm4xwgbjqet"></path>
                                        </g>
                                        <path
                                            d="M91.81,374.32c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.75,0-10.43,14,0,19.38S81.39,379.67,91.81,374.32Z"
                                            style="fill: #314A48; transform-origin: 72.9369px 364.63px;"
                                            id="el71eern0wexm" class="animable"></path>
                                        <g id="ellra8mg4lcxf">
                                            <path
                                                d="M91.81,374.32c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.75,0-10.43,14,0,19.38S81.39,379.67,91.81,374.32Z"
                                                style="opacity: 0.45; transform-origin: 72.9369px 364.63px;"
                                                class="animable" id="elyc2npydmf1"></path>
                                        </g>
                                        <path
                                            d="M91.81,358.57c-10.42-5.35-27.33-5.35-37.75,0-4.31,2.21-6.84,5-7.58,7.87.74,2.88,3.27,5.66,7.58,7.88,10.42,5.35,27.33,5.35,37.75,0,4.32-2.22,6.84-5,7.59-7.88C98.65,363.56,96.13,360.78,91.81,358.57Z"
                                            style="fill: #314A48; transform-origin: 72.94px 366.445px;"
                                            id="elaw4w6br04f" class="animable"></path>
                                        <g id="elxkkj1o2f87">
                                            <path
                                                d="M91.81,358.57c-10.42-5.35-27.33-5.35-37.75,0-4.31,2.21-6.84,5-7.58,7.87.74,2.88,3.27,5.66,7.58,7.88,10.42,5.35,27.33,5.35,37.75,0,4.32-2.22,6.84-5,7.59-7.88C98.65,363.56,96.13,360.78,91.81,358.57Z"
                                                style="opacity: 0.3; transform-origin: 72.94px 366.445px;"
                                                class="animable" id="elz6pji0k5c"></path>
                                        </g>
                                        <polygon
                                            points="75.47 355.15 75.47 359.06 74.13 360.48 71.66 363.06 71.66 358.99 75.47 355.15"
                                            style="fill: #314A48; transform-origin: 73.565px 359.105px;"
                                            id="elvexglxfsqx" class="animable"></polygon>
                                        <g id="elx2ck4ymwws">
                                            <polygon
                                                points="75.47 355.15 75.47 359.06 74.13 360.48 71.66 363.06 71.66 358.99 75.47 355.15"
                                                style="opacity: 0.3; transform-origin: 73.565px 359.105px;"
                                                class="animable" id="el1l8ph57bpyv"></polygon>
                                        </g>
                                        <path
                                            d="M71.66,359v2.31a22.58,22.58,0,0,1-3.8,1.08,4.16,4.16,0,0,1-1.63-.07,3.73,3.73,0,0,1-.78-.31c-.94-.57-1.09-1.4.7-2.44A6.27,6.27,0,0,1,71.66,359Z"
                                            style="fill: #314A48; transform-origin: 68.2129px 360.525px;"
                                            id="elnsnjbgd6sts" class="animable"></path>
                                        <g id="elfq4urrecqc">
                                            <path
                                                d="M71.66,359v2.31a22.58,22.58,0,0,1-3.8,1.08,4.16,4.16,0,0,1-1.63-.07,3.73,3.73,0,0,1-.78-.31c-.94-.57-1.09-1.4.7-2.44A6.27,6.27,0,0,1,71.66,359Z"
                                                style="opacity: 0.45; transform-origin: 68.2129px 360.525px;"
                                                class="animable" id="elopm61toco0q"></path>
                                        </g>
                                        <path
                                            d="M89.38,370.1v3.38L85.1,376l-3.17-1.83a16.15,16.15,0,0,1-12.26.56v-3.39l1.94-1.93-1.94.54a12.56,12.56,0,0,1-9.56-1.09c-1.92-1.11-2.81-2.32-2.8-3.57V362l.34-1.44-1.16-.68v-3.38l3.16,1.83c-1.54,1.2-2.36,2.43-2.34,3.63v0c0,1.22.93,2.4,2.8,3.48,4.17,2.4,8.29,1.61,12.07.4,3.52-1.15,6.08-2.5,8.11-1.33,1.25.73.87,1.61-.54,2.43a7,7,0,0,1-6,.35l-4,4a14.48,14.48,0,0,0,2.23.66,16.61,16.61,0,0,0,10-1.21l3.17,1.83Z"
                                            style="fill: #314A48; transform-origin: 72.935px 366.25px;"
                                            id="elesud7jt7358" class="animable"></path>
                                        <g id="elkstiacxu3kj">
                                            <path
                                                d="M57.31,361.91v0c0,1.22.93,2.4,2.8,3.48,4.17,2.4,8.29,1.61,12.07.4,3.52-1.15,6.08-2.5,8.11-1.33,1.25.73.87,1.61-.54,2.43a7,7,0,0,1-6,.35l-4,4a14.48,14.48,0,0,0,2.23.66,16.61,16.61,0,0,0,10-1.21l3.17,1.83,4.28-2.47v3.38L85.1,376l-3.17-1.83a16.15,16.15,0,0,1-12.26.56v-3.39l1.94-1.93-1.94.54a12.56,12.56,0,0,1-9.56-1.09c-1.92-1.11-2.81-2.32-2.8-3.57V362l.34-1.44"
                                                style="opacity: 0.2; transform-origin: 73.37px 368.28px;"
                                                class="animable" id="el0s2hr7ikvl9"></path>
                                        </g>
                                        <g id="el8wb6yn16kxp">
                                            <path d="M56.49,356.45v3.38l1.16.68h0a6.86,6.86,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 58.07px 358.48px;"
                                                class="animable" id="el8035mccutts"></path>
                                        </g>
                                        <path
                                            d="M88.67,367.89a4,4,0,0,1-.32,1.62l-2.15-1.24A4.22,4.22,0,0,0,88.64,365C88.65,366,88.67,367.8,88.67,367.89Z"
                                            style="fill: #314A48; transform-origin: 87.4352px 367.255px;"
                                            id="el8y53ec61j37" class="animable"></path>
                                        <g id="elmm8nbmv40r">
                                            <g style="opacity: 0.3; transform-origin: 87.4352px 367.255px;"
                                                class="animable" id="elc4gyhhv5zg">
                                                <path
                                                    d="M88.67,367.89a4,4,0,0,1-.32,1.62l-2.15-1.24A4.22,4.22,0,0,0,88.64,365C88.65,366,88.67,367.8,88.67,367.89Z"
                                                    id="elhoggwl3lgj5" class="animable"
                                                    style="transform-origin: 87.4352px 367.255px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M88.66,369.69l-2.45-1.42a4.18,4.18,0,0,0,2.45-3.75,3.25,3.25,0,0,0,0-.33c-.11-1.17-1-2.3-2.74-3.32h0a11.17,11.17,0,0,0-5.69-1.49h-.54l-.51,0a3.44,3.44,0,0,0-.45,0l-.18,0-.34,0a24.24,24.24,0,0,0-2.77.57l-1.29.38-1.8.59-.67.23a20.81,20.81,0,0,1-3.8,1.08,3.86,3.86,0,0,1-1.64-.06,3.15,3.15,0,0,1-.76-.32c-.95-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.57l3.8-3.84a16.24,16.24,0,0,0-11.48.64L60.8,354l-4.31,2.49,3.17,1.83c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.81,3.48,4.17,2.41,8.3,1.61,12.08.39,3.51-1.14,6.06-2.49,8.09-1.32,1.26.73.88,1.61-.53,2.42a6.93,6.93,0,0,1-6,.35l-4,4a16.55,16.55,0,0,0,2.24.65,14.93,14.93,0,0,0,3.91.29,18,18,0,0,0,6.11-1.5l3.18,1.84,4.27-2.47Z"
                                            style="fill: #314A48; transform-origin: 72.975px 363.28px;"
                                            id="eloruy540br0h" class="animable"></path>
                                        <g id="eljeaxs85bren">
                                            <g style="opacity: 0.3; transform-origin: 72.975px 363.28px;"
                                                class="animable" id="eluj2swadfa6a">
                                                <path
                                                    d="M88.66,369.69l-2.45-1.42a4.18,4.18,0,0,0,2.45-3.75,3.25,3.25,0,0,0,0-.33c-.11-1.17-1-2.3-2.74-3.32h0a11.17,11.17,0,0,0-5.69-1.49h-.54l-.51,0a3.44,3.44,0,0,0-.45,0l-.18,0-.34,0a24.24,24.24,0,0,0-2.77.57l-1.29.38-1.8.59-.67.23a20.81,20.81,0,0,1-3.8,1.08,3.86,3.86,0,0,1-1.64-.06,3.15,3.15,0,0,1-.76-.32c-.95-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.57l3.8-3.84a16.24,16.24,0,0,0-11.48.64L60.8,354l-4.31,2.49,3.17,1.83c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.81,3.48,4.17,2.41,8.3,1.61,12.08.39,3.51-1.14,6.06-2.49,8.09-1.32,1.26.73.88,1.61-.53,2.42a6.93,6.93,0,0,1-6,.35l-4,4a16.55,16.55,0,0,0,2.24.65,14.93,14.93,0,0,0,3.91.29,18,18,0,0,0,6.11-1.5l3.18,1.84,4.27-2.47Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 72.975px 363.28px;"
                                                    id="elth8l82hxmtl" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon points="81.93 370.74 81.93 374.12 85.1 375.95 85.1 372.57 81.93 370.74"
                                            style="fill: #314A48; transform-origin: 83.515px 373.345px;"
                                            id="el55iauz5nru9" class="animable"></polygon>
                                        <g id="elc9upzvcxmj7">
                                            <polygon
                                                points="81.93 370.74 81.93 374.12 85.1 375.95 85.1 372.57 81.93 370.74"
                                                style="opacity: 0.45; transform-origin: 83.515px 373.345px;"
                                                class="animable" id="elqxnk0c0blci"></polygon>
                                        </g>
                                        <g id="el9zi3jbi80r">
                                            <polygon
                                                points="56.49 356.45 60.8 353.96 63.98 355.79 60.83 354.47 56.49 356.45"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 60.235px 355.205px;"
                                                class="animable" id="elnxjg3inf7yd"></polygon>
                                        </g>
                                        <g id="elxpbf51pdpv">
                                            <path
                                                d="M66.15,359.56a6.27,6.27,0,0,1,5.51-.57l3.8-3.84-3.8,3.44A5.78,5.78,0,0,0,66.15,359.56Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 70.805px 357.355px;"
                                                class="animable" id="el1y36gw4ir3s"></path>
                                        </g>
                                        <g id="eldsr5a05rx2d">
                                            <path
                                                d="M69.66,371.3a16.55,16.55,0,0,0,2.24.65,14.93,14.93,0,0,0,3.91.29,13.8,13.8,0,0,1-5.51-1.1l3.41-3.87Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 72.735px 369.767px;"
                                                class="animable" id="elbbvq0674rur"></path>
                                        </g>
                                        <g id="elglynnlqlrr9">
                                            <polygon
                                                points="81.93 370.74 85.1 372.17 89.37 370.11 85.1 372.57 81.93 370.74"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 85.65px 371.34px;"
                                                class="animable" id="elf3miob18tcl"></polygon>
                                        </g>
                                        <g id="el45wsxqckz46">
                                            <path
                                                d="M44.17,349.47c11.24-6.83,28.76-7.5,28.76-7.5-11,0-21.34,2.46-29.08,6.93-7.43,4.29-11.51,9.87-11.51,15.73C32.34,364.63,32.74,356.42,44.17,349.47Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 52.635px 353.3px;"
                                                class="animable" id="eldrz0uizm5da"></path>
                                        </g>
                                        <g id="elorw6vms7s1c">
                                            <path
                                                d="M69.66,389.07c11.93.53,24.17-1.84,33.29-7.11,9.49-5.48,13.55-12.9,12.18-20,0,0,1.77,11.65-12.89,19.61S69.66,389.07,69.66,389.07Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 92.5246px 375.551px;"
                                                class="animable" id="elfpipijqveiw"></path>
                                        </g>
                                        <path
                                            d="M110.3,352.6a27.36,27.36,0,0,0-10.14-9.38c-16.57-9.57-43.45-9.57-60,0A27.44,27.44,0,0,0,30,352.6H27.7v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.46,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0V352.6Z"
                                            style="fill: #314A48; transform-origin: 70.12px 360.55px;"
                                            id="elkn8onzlw9qc" class="animable"></path>
                                        <g id="el54kp7bwezqe">
                                            <g style="opacity: 0.25; transform-origin: 70.12px 360.55px;"
                                                class="animable" id="ela6csrm22dhg">
                                                <path
                                                    d="M110.3,352.6a27.36,27.36,0,0,0-10.14-9.38c-16.57-9.57-43.45-9.57-60,0A27.44,27.44,0,0,0,30,352.6H27.7v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.46,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0V352.6Z"
                                                    id="elv3677k4xoyi" class="animable"
                                                    style="transform-origin: 70.12px 360.55px;"></path>
                                            </g>
                                        </g>
                                        <g id="elowuqevaheon">
                                            <path
                                                d="M70.15,336c-10.86,0-21.73,2.4-30,7.18A27.44,27.44,0,0,0,30,352.6H27.7v8.67h0c.3,6,4.43,12,12.4,16.61,8.29,4.79,19.16,7.18,30,7.18Z"
                                                style="opacity: 0.1; transform-origin: 48.925px 360.53px;"
                                                class="animable" id="elywp04crdg9p"></path>
                                        </g>
                                        <g id="el70mljo7x5p">
                                            <path
                                                d="M47,340a48.11,48.11,0,0,0-6.83,3.2A27.44,27.44,0,0,0,30,352.6H27.7v8.67h0c.3,6,4.43,12,12.4,16.61a46,46,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 37.35px 360.54px;"
                                                class="animable" id="el2pdp9s1chss"></path>
                                        </g>
                                        <g id="el17vs91vwt6o">
                                            <path
                                                d="M93.33,340a47.6,47.6,0,0,1,6.83,3.2,27.36,27.36,0,0,1,10.14,9.38h2.3v8.67h0c-.31,6-4.46,12-12.44,16.61a45.54,45.54,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 102.965px 360.53px;"
                                                class="animable" id="eljr1c82fpsxe"></path>
                                        </g>
                                        <path
                                            d="M100.16,335.27c-16.57-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.46,9.58,60,0S116.74,344.85,100.16,335.27Z"
                                            style="fill: #314A48; transform-origin: 70.1581px 352.602px;"
                                            id="ell8mltbmdjy" class="animable"></path>
                                        <g id="els5en6z9n7q7">
                                            <path
                                                d="M100.16,335.27c-16.57-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.46,9.58,60,0S116.74,344.85,100.16,335.27Z"
                                                style="opacity: 0.2; transform-origin: 70.1581px 352.602px;"
                                                class="animable" id="eldvjjlebq8o"></path>
                                        </g>
                                        <path
                                            d="M89.7,341.62c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.55,4.17c-5.55,2.84-8.61,6.75-8.61,11s3.06,8.13,8.61,11c5.25,2.7,12.19,4.18,19.55,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S95.25,344.46,89.7,341.62Z"
                                            style="fill: #314A48; transform-origin: 70.15px 352.625px;"
                                            id="el17lpejth7ib" class="animable"></path>
                                        <g id="elt1fc51g0ia">
                                            <path
                                                d="M89.7,341.62c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.55,4.17c-5.55,2.84-8.61,6.75-8.61,11s3.06,8.13,8.61,11c5.25,2.7,12.19,4.18,19.55,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S95.25,344.46,89.7,341.62Z"
                                                style="opacity: 0.4; transform-origin: 70.15px 352.625px;"
                                                class="animable" id="ely9hnuna1mya"></path>
                                        </g>
                                        <path
                                            d="M99.24,336.88c-7.74-4.47-18.07-6.94-29.09-6.94s-21.35,2.47-29.09,6.94c-7.43,4.28-11.51,9.86-11.51,15.73s4.08,11.44,11.51,15.73c7.74,4.46,18.07,6.93,29.09,6.93s21.35-2.47,29.09-6.93c7.42-4.29,11.51-9.88,11.51-15.73S106.66,341.16,99.24,336.88ZM89.7,363.59c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.55-4.18c-5.55-2.85-8.61-6.75-8.61-11s3.06-8.15,8.61-11c5.25-2.69,12.19-4.17,19.55-4.17s14.3,1.48,19.55,4.17c5.55,2.84,8.61,6.75,8.61,11S95.25,360.74,89.7,363.59Z"
                                            style="fill: #314A48; transform-origin: 70.15px 352.605px;"
                                            id="elkpn06s6nn9" class="animable"></path>
                                        <g id="elh94cjgs8hx4">
                                            <path
                                                d="M99.24,336.88c-7.74-4.47-18.07-6.94-29.09-6.94s-21.35,2.47-29.09,6.94c-7.43,4.28-11.51,9.86-11.51,15.73s4.08,11.44,11.51,15.73c7.74,4.46,18.07,6.93,29.09,6.93s21.35-2.47,29.09-6.93c7.42-4.29,11.51-9.88,11.51-15.73S106.66,341.16,99.24,336.88ZM89.7,363.59c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.55-4.18c-5.55-2.85-8.61-6.75-8.61-11s3.06-8.15,8.61-11c5.25-2.69,12.19-4.17,19.55-4.17s14.3,1.48,19.55,4.17c5.55,2.84,8.61,6.75,8.61,11S95.25,360.74,89.7,363.59Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 70.15px 352.605px;"
                                                class="animable" id="elf5nj6ynvns5"></path>
                                        </g>
                                        <g id="elrja2examgqj">
                                            <path
                                                d="M89,362.29c10.42-5.35,10.42-14,0-19.38s-27.33-5.35-37.76,0-10.43,14,0,19.38S78.6,367.64,89,362.29Z"
                                                style="opacity: 0.2; transform-origin: 70.1163px 352.6px;"
                                                class="animable" id="elmkspxmizfza"></path>
                                        </g>
                                        <path
                                            d="M89,362.29c10.42-5.35,10.42-14,0-19.38s-27.33-5.35-37.76,0-10.43,14,0,19.38S78.6,367.64,89,362.29Z"
                                            style="fill: #314A48; transform-origin: 70.1163px 352.6px;"
                                            id="elja6tydb0b58" class="animable"></path>
                                        <g id="elwyvlth0m7mm">
                                            <path
                                                d="M89,362.29c10.42-5.35,10.42-14,0-19.38s-27.33-5.35-37.76,0-10.43,14,0,19.38S78.6,367.64,89,362.29Z"
                                                style="opacity: 0.45; transform-origin: 70.1163px 352.6px;"
                                                class="animable" id="el6wj1oe4gga"></path>
                                        </g>
                                        <path
                                            d="M89,346.54c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.22-6.84,5-7.58,7.88.74,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C95.86,351.54,93.34,348.76,89,346.54Z"
                                            style="fill: #314A48; transform-origin: 70.12px 354.415px;"
                                            id="el08u4zt7tp67o" class="animable"></path>
                                        <g id="elltq8ero0z9f">
                                            <path
                                                d="M89,346.54c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.22-6.84,5-7.58,7.88.74,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C95.86,351.54,93.34,348.76,89,346.54Z"
                                                style="opacity: 0.3; transform-origin: 70.12px 354.415px;"
                                                class="animable" id="elyqc5c1j6ht"></path>
                                        </g>
                                        <polygon
                                            points="72.68 343.12 72.68 347.04 71.34 348.45 68.87 351.04 68.87 346.96 72.68 343.12"
                                            style="fill: #314A48; transform-origin: 70.775px 347.08px;"
                                            id="eltf72bdzkxah" class="animable"></polygon>
                                        <g id="elk3bt29ihuui">
                                            <polygon
                                                points="72.68 343.12 72.68 347.04 71.34 348.45 68.87 351.04 68.87 346.96 72.68 343.12"
                                                style="opacity: 0.3; transform-origin: 70.775px 347.08px;"
                                                class="animable" id="elmmzq6ess5gn"></polygon>
                                        </g>
                                        <path
                                            d="M68.87,347v2.31a22.32,22.32,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.69,3.69,0,0,1-.77-.32c-1-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,68.87,347Z"
                                            style="fill: #314A48; transform-origin: 65.4139px 348.53px;"
                                            id="el9og0r41jyk" class="animable"></path>
                                        <g id="el6hullpwt1a9">
                                            <path
                                                d="M68.87,347v2.31a22.32,22.32,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.69,3.69,0,0,1-.77-.32c-1-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,68.87,347Z"
                                                style="opacity: 0.45; transform-origin: 65.4139px 348.53px;"
                                                class="animable" id="el55elav2ukmb"></path>
                                        </g>
                                        <path
                                            d="M86.59,358.08v3.38l-4.28,2.47-3.17-1.83a16.15,16.15,0,0,1-12.26.55v-3.38l1.94-1.93-1.94.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.1-2.81-2.32-2.81-3.57v-3.28l.35-1.44-1.17-.68v-3.39l3.17,1.84c-1.55,1.19-2.37,2.43-2.35,3.63v0c0,1.21.93,2.39,2.81,3.47,4.16,2.41,8.29,1.61,12.07.4,3.51-1.15,6.07-2.49,8.1-1.33,1.26.73.87,1.62-.54,2.43a7,7,0,0,1-6,.35l-4.05,4a14,14,0,0,0,2.23.65,16.67,16.67,0,0,0,10-1.2l3.17,1.83Z"
                                            style="fill: #314A48; transform-origin: 70.145px 354.175px;"
                                            id="elva6sq1c8hm" class="animable"></path>
                                        <g id="eldjm19omnzyf">
                                            <path
                                                d="M54.52,349.89v0c0,1.21.93,2.39,2.81,3.47,4.16,2.41,8.29,1.61,12.07.4,3.51-1.15,6.07-2.49,8.1-1.33,1.26.73.87,1.62-.54,2.43a7,7,0,0,1-6,.35l-4.05,4a14,14,0,0,0,2.23.65,16.67,16.67,0,0,0,10-1.2l3.17,1.83,4.28-2.47v3.38l-4.28,2.47-3.17-1.83a16.15,16.15,0,0,1-12.26.55v-3.38l1.94-1.93-1.94.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.1-2.81-2.32-2.81-3.57v-3.28l.35-1.44"
                                                style="opacity: 0.2; transform-origin: 70.555px 356.15px;"
                                                class="animable" id="elwdweb08c9p"></path>
                                        </g>
                                        <g id="el4av1ox9uq9x">
                                            <path d="M53.7,344.42v3.39l1.17.68h0a6.79,6.79,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 55.285px 346.455px;"
                                                class="animable" id="el1grevo7ee4l"></path>
                                        </g>
                                        <path
                                            d="M85.88,355.87a3.91,3.91,0,0,1-.32,1.61l-2.14-1.23A4.2,4.2,0,0,0,85.85,353C85.86,353.92,85.88,355.78,85.88,355.87Z"
                                            style="fill: #314A48; transform-origin: 84.6502px 355.24px;"
                                            id="elkxd801lk5wf" class="animable"></path>
                                        <g id="elgt3zgn47k54">
                                            <g style="opacity: 0.3; transform-origin: 84.6502px 355.24px;"
                                                class="animable" id="el68h29qd45n4">
                                                <path
                                                    d="M85.88,355.87a3.91,3.91,0,0,1-.32,1.61l-2.14-1.23A4.2,4.2,0,0,0,85.85,353C85.86,353.92,85.88,355.78,85.88,355.87Z"
                                                    id="elq0lxznf1rtl" class="animable"
                                                    style="transform-origin: 84.6502px 355.24px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M85.87,357.67l-2.45-1.42a4.18,4.18,0,0,0,2.45-3.76c0-.12,0-.22,0-.33-.1-1.17-1-2.3-2.73-3.32h0a11.27,11.27,0,0,0-5.7-1.48h-.54l-.51,0-.45,0-.18,0-.34,0a22.66,22.66,0,0,0-2.77.58c-.43.11-.87.24-1.29.38-.62.19-1.22.39-1.79.58l-.68.24a21.19,21.19,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.07,3.65,3.65,0,0,1-.77-.31c-1-.56-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.58l3.81-3.84a16.37,16.37,0,0,0-11.49.64L58,341.94l-4.32,2.49,3.17,1.82c-1.55,1.2-2.38,2.44-2.35,3.64v0c0,1.22.93,2.4,2.81,3.48,4.17,2.41,8.29,1.62,12.07.4,3.51-1.15,6.07-2.5,8.09-1.33,1.27.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.06,4a15.66,15.66,0,0,0,2.24.66,15,15,0,0,0,3.91.28,18,18,0,0,0,6.12-1.49l3.17,1.83,4.27-2.47Z"
                                            style="fill: #314A48; transform-origin: 70.135px 351.22px;"
                                            id="el92nv7bplgwr" class="animable"></path>
                                        <g id="ellsp5aju9e58">
                                            <g style="opacity: 0.3; transform-origin: 70.135px 351.22px;"
                                                class="animable" id="eljxy5doolbtd">
                                                <path
                                                    d="M85.87,357.67l-2.45-1.42a4.18,4.18,0,0,0,2.45-3.76c0-.12,0-.22,0-.33-.1-1.17-1-2.3-2.73-3.32h0a11.27,11.27,0,0,0-5.7-1.48h-.54l-.51,0-.45,0-.18,0-.34,0a22.66,22.66,0,0,0-2.77.58c-.43.11-.87.24-1.29.38-.62.19-1.22.39-1.79.58l-.68.24a21.19,21.19,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.07,3.65,3.65,0,0,1-.77-.31c-1-.56-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.58l3.81-3.84a16.37,16.37,0,0,0-11.49.64L58,341.94l-4.32,2.49,3.17,1.82c-1.55,1.2-2.38,2.44-2.35,3.64v0c0,1.22.93,2.4,2.81,3.48,4.17,2.41,8.29,1.62,12.07.4,3.51-1.15,6.07-2.5,8.09-1.33,1.27.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.06,4a15.66,15.66,0,0,0,2.24.66,15,15,0,0,0,3.91.28,18,18,0,0,0,6.12-1.49l3.17,1.83,4.27-2.47Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 70.135px 351.22px;"
                                                    id="elt4gdrtctdjr" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="79.14 358.72 79.14 362.1 82.31 363.93 82.31 360.55 79.14 358.72"
                                            style="fill: #314A48; transform-origin: 80.725px 361.325px;"
                                            id="elqmcj0yqhth" class="animable"></polygon>
                                        <g id="el83to86cvbpb">
                                            <polygon
                                                points="79.14 358.72 79.14 362.1 82.31 363.93 82.31 360.55 79.14 358.72"
                                                style="opacity: 0.45; transform-origin: 80.725px 361.325px;"
                                                class="animable" id="elq68zyrukid"></polygon>
                                        </g>
                                        <g id="elbqp3u51wwi8">
                                            <polygon
                                                points="53.7 344.43 58.02 341.94 61.19 343.76 58.04 342.44 53.7 344.43"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 57.445px 343.185px;"
                                                class="animable" id="eln6cjgsearc9"></polygon>
                                        </g>
                                        <g id="eloa4tp986gbp">
                                            <path
                                                d="M63.36,347.54a6.27,6.27,0,0,1,5.51-.58l3.81-3.84-3.81,3.45A5.78,5.78,0,0,0,63.36,347.54Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 68.02px 345.33px;"
                                                class="animable" id="el4cpd256twh7"></path>
                                        </g>
                                        <g id="el42f9t5qlw28">
                                            <path
                                                d="M66.87,359.27a15.66,15.66,0,0,0,2.24.66,15,15,0,0,0,3.91.28,13.8,13.8,0,0,1-5.51-1.1l3.42-3.86Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 69.945px 357.743px;"
                                                class="animable" id="elgg1ar9purw5"></path>
                                        </g>
                                        <g id="elf25fg5cjmw">
                                            <polygon
                                                points="79.14 358.72 82.31 360.15 86.58 358.08 82.31 360.55 79.14 358.72"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 82.86px 359.315px;"
                                                class="animable" id="elohzndt1zca"></polygon>
                                        </g>
                                        <g id="elf3gqmc9fgub">
                                            <path
                                                d="M41.38,337.45c11.24-6.83,28.77-7.51,28.77-7.51-11,0-21.35,2.47-29.09,6.94-7.43,4.28-11.51,9.86-11.51,15.73C29.55,352.61,30,344.39,41.38,337.45Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 49.85px 341.275px;"
                                                class="animable" id="elwbi3kobbsqs"></path>
                                        </g>
                                        <g id="elk5r2kb0pkh">
                                            <path
                                                d="M66.87,377c11.93.53,24.17-1.84,33.29-7.11,9.49-5.47,13.55-12.9,12.18-20,0,0,1.77,11.64-12.89,19.61S66.87,377,66.87,377Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 89.7346px 363.481px;"
                                                class="animable" id="elctes9q95ao"></path>
                                        </g>
                                        <path
                                            d="M116.38,340.45a27.44,27.44,0,0,0-10.14-9.38c-16.58-9.57-43.46-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38h-2.3v8.67h0c.31,6,4.44,12,12.41,16.61,16.57,9.57,43.45,9.57,60,0,8-4.6,12.13-10.58,12.43-16.61h0v-8.67Z"
                                            style="fill: #314A48; transform-origin: 76.22px 348.4px;" id="el9lqsic556js"
                                            class="animable"></path>
                                        <g id="eldgcji2kg7yf">
                                            <g style="opacity: 0.25; transform-origin: 76.22px 348.4px;"
                                                class="animable" id="elrmxr82p5ou">
                                                <path
                                                    d="M116.38,340.45a27.44,27.44,0,0,0-10.14-9.38c-16.58-9.57-43.46-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38h-2.3v8.67h0c.31,6,4.44,12,12.41,16.61,16.57,9.57,43.45,9.57,60,0,8-4.6,12.13-10.58,12.43-16.61h0v-8.67Z"
                                                    id="eliu5dzsf2a7r" class="animable"
                                                    style="transform-origin: 76.22px 348.4px;"></path>
                                            </g>
                                        </g>
                                        <g id="elrxj61czh7tp">
                                            <path
                                                d="M76.23,323.89c-10.87,0-21.73,2.4-30,7.18a27.36,27.36,0,0,0-10.14,9.38h-2.3v8.67h0c.31,6,4.44,12,12.41,16.61,8.29,4.79,19.15,7.18,30,7.18Z"
                                                style="opacity: 0.1; transform-origin: 55.01px 348.4px;"
                                                class="animable" id="eluynj69509q"></path>
                                        </g>
                                        <g id="elchjl4sb9s">
                                            <path
                                                d="M53,327.87a46.55,46.55,0,0,0-6.83,3.2,27.36,27.36,0,0,0-10.14,9.38h-2.3v8.67h0c.31,6,4.44,12,12.41,16.61a45.54,45.54,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 43.365px 348.4px;"
                                                class="animable" id="el84m3y5a1wuh"></path>
                                        </g>
                                        <g id="elwcmm9iif1p">
                                            <path
                                                d="M99.41,327.87a47,47,0,0,1,6.83,3.2,27.44,27.44,0,0,1,10.14,9.38h2.29v8.67h0c-.3,6-4.46,12-12.43,16.61a46,46,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 109.04px 348.4px;"
                                                class="animable" id="elajjf4c0gton"></path>
                                        </g>
                                        <path
                                            d="M106.24,323.12c-16.58-9.57-43.46-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.58,60,0S122.82,332.7,106.24,323.12Z"
                                            style="fill: #314A48; transform-origin: 76.2419px 340.452px;"
                                            id="elwx6uhq86gc" class="animable"></path>
                                        <g id="elho7bug0udq">
                                            <path
                                                d="M106.24,323.12c-16.58-9.57-43.46-9.57-60,0s-16.58,25.09,0,34.66,43.45,9.58,60,0S122.82,332.7,106.24,323.12Z"
                                                style="opacity: 0.2; transform-origin: 76.2419px 340.452px;"
                                                class="animable" id="el3pjpoxmlwns"></path>
                                        </g>
                                        <path
                                            d="M95.77,329.47c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.84-8.62,6.75-8.62,11s3.06,8.13,8.62,11c5.24,2.7,12.19,4.18,19.54,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S101.32,332.31,95.77,329.47Z"
                                            style="fill: #314A48; transform-origin: 76.22px 340.475px;"
                                            id="elxqbaho3oc3p" class="animable"></path>
                                        <g id="el4x3t0nudoih">
                                            <path
                                                d="M95.77,329.47c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.84-8.62,6.75-8.62,11s3.06,8.13,8.62,11c5.24,2.7,12.19,4.18,19.54,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S101.32,332.31,95.77,329.47Z"
                                                style="opacity: 0.4; transform-origin: 76.22px 340.475px;"
                                                class="animable" id="elgmce8w8f26"></path>
                                        </g>
                                        <path
                                            d="M105.32,324.73c-7.74-4.47-18.08-6.94-29.1-6.94s-21.35,2.47-29.09,6.94c-7.42,4.28-11.51,9.86-11.51,15.73s4.09,11.44,11.51,15.73c7.74,4.46,18.07,6.92,29.09,6.92s21.36-2.46,29.1-6.92c7.41-4.29,11.51-9.88,11.51-15.73S112.73,329,105.32,324.73Zm-9.55,26.71c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.54-4.18c-5.56-2.85-8.62-6.75-8.62-11s3.06-8.15,8.62-11c5.24-2.69,12.19-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.84,8.61,6.75,8.61,11S101.32,348.59,95.77,351.44Z"
                                            style="fill: #314A48; transform-origin: 76.225px 340.45px;"
                                            id="elcjyzdj3nz7c" class="animable"></path>
                                        <g id="ellqayhqag2t">
                                            <path
                                                d="M105.32,324.73c-7.74-4.47-18.08-6.94-29.1-6.94s-21.35,2.47-29.09,6.94c-7.42,4.28-11.51,9.86-11.51,15.73s4.09,11.44,11.51,15.73c7.74,4.46,18.07,6.92,29.09,6.92s21.36-2.46,29.1-6.92c7.41-4.29,11.51-9.88,11.51-15.73S112.73,329,105.32,324.73Zm-9.55,26.71c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.54-4.18c-5.56-2.85-8.62-6.75-8.62-11s3.06-8.15,8.62-11c5.24-2.69,12.19-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.84,8.61,6.75,8.61,11S101.32,348.59,95.77,351.44Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 76.225px 340.45px;"
                                                class="animable" id="eltzu1khzkcc"></path>
                                        </g>
                                        <g id="el8qe95r4i5oe">
                                            <path
                                                d="M95.1,350.14c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.76,0-10.42,14,0,19.38S84.68,355.49,95.1,350.14Z"
                                                style="opacity: 0.2; transform-origin: 76.2219px 340.45px;"
                                                class="animable" id="el88k106jsr6j"></path>
                                        </g>
                                        <path
                                            d="M95.1,350.14c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.76,0-10.42,14,0,19.38S84.68,355.49,95.1,350.14Z"
                                            style="fill: #314A48; transform-origin: 76.2219px 340.45px;"
                                            id="elp38hawq3m6r" class="animable"></path>
                                        <g id="eluyri57shvp">
                                            <path
                                                d="M95.1,350.14c10.43-5.35,10.43-14,0-19.38s-27.33-5.35-37.76,0-10.42,14,0,19.38S84.68,355.49,95.1,350.14Z"
                                                style="opacity: 0.45; transform-origin: 76.2219px 340.45px;"
                                                class="animable" id="elr2zmt27ufc"></path>
                                        </g>
                                        <path
                                            d="M95.1,334.39c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.22-6.83,5-7.58,7.88.75,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.34,5.35,37.76,0,4.31-2.21,6.84-5,7.58-7.87C101.94,339.39,99.41,336.61,95.1,334.39Z"
                                            style="fill: #314A48; transform-origin: 76.22px 342.265px;"
                                            id="elinyf78e9wk" class="animable"></path>
                                        <g id="eltdrbgnqdgp">
                                            <path
                                                d="M95.1,334.39c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.22-6.83,5-7.58,7.88.75,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.34,5.35,37.76,0,4.31-2.21,6.84-5,7.58-7.87C101.94,339.39,99.41,336.61,95.1,334.39Z"
                                                style="opacity: 0.3; transform-origin: 76.22px 342.265px;"
                                                class="animable" id="elgnxknc6hkmj"></path>
                                        </g>
                                        <polygon
                                            points="78.75 330.97 78.75 334.89 77.42 336.3 74.94 338.89 74.94 334.81 78.75 330.97"
                                            style="fill: #314A48; transform-origin: 76.845px 334.93px;"
                                            id="elths9y2sx8q" class="animable"></polygon>
                                        <g id="elqamnntim06r">
                                            <polygon
                                                points="78.75 330.97 78.75 334.89 77.42 336.3 74.94 338.89 74.94 334.81 78.75 330.97"
                                                style="opacity: 0.3; transform-origin: 76.845px 334.93px;"
                                                class="animable" id="elpu5p8nagngp"></polygon>
                                        </g>
                                        <path
                                            d="M74.94,334.81v2.31a22.07,22.07,0,0,1-3.8,1.08,3.93,3.93,0,0,1-1.62-.06,3.77,3.77,0,0,1-.78-.32c-.94-.56-1.09-1.4.7-2.43A6.25,6.25,0,0,1,74.94,334.81Z"
                                            style="fill: #314A48; transform-origin: 71.4979px 336.341px;"
                                            id="el5361ej0f7md" class="animable"></path>
                                        <g id="el8uo4xnh85si">
                                            <path
                                                d="M74.94,334.81v2.31a22.07,22.07,0,0,1-3.8,1.08,3.93,3.93,0,0,1-1.62-.06,3.77,3.77,0,0,1-.78-.32c-.94-.56-1.09-1.4.7-2.43A6.25,6.25,0,0,1,74.94,334.81Z"
                                                style="opacity: 0.45; transform-origin: 71.4979px 336.341px;"
                                                class="animable" id="el7xths98mskd"></path>
                                        </g>
                                        <path
                                            d="M92.66,345.93v3.38l-4.28,2.47L85.22,350A16.18,16.18,0,0,1,73,350.5v-3.38l2-1.93-2,.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.11-2.81-2.32-2.81-3.57v-3.29l.35-1.43-1.17-.68v-3.39l3.17,1.84c-1.55,1.19-2.37,2.43-2.35,3.63v0c0,1.22.94,2.4,2.81,3.48,4.17,2.41,8.29,1.61,12.07.4,3.52-1.15,6.08-2.49,8.1-1.33,1.26.73.88,1.62-.53,2.43a7,7,0,0,1-6,.35l-4,4a13.79,13.79,0,0,0,2.24.65,16.69,16.69,0,0,0,10-1.2l3.16,1.83Z"
                                            style="fill: #314A48; transform-origin: 76.24px 342.025px;"
                                            id="el0wwm4e1n4ye" class="animable"></path>
                                        <g id="el19zzvm1s06w">
                                            <path
                                                d="M60.59,337.74v0c0,1.22.94,2.4,2.81,3.48,4.17,2.41,8.29,1.61,12.07.4,3.52-1.15,6.08-2.49,8.1-1.33,1.26.73.88,1.62-.53,2.43a7,7,0,0,1-6,.35l-4,4a13.79,13.79,0,0,0,2.24.65,16.69,16.69,0,0,0,10-1.2l3.16,1.83,4.28-2.47v3.38l-4.28,2.47L85.22,350A16.18,16.18,0,0,1,73,350.5v-3.38l2-1.93-2,.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.11-2.81-2.32-2.81-3.57v-3.29l.35-1.43"
                                                style="opacity: 0.2; transform-origin: 76.655px 344.035px;"
                                                class="animable" id="elk9g830g0akq"></path>
                                        </g>
                                        <g id="eley3vn9q5jyk">
                                            <path d="M59.77,332.27v3.39l1.17.68h0a6.93,6.93,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 61.355px 334.305px;"
                                                class="animable" id="elr2zput1etu"></path>
                                        </g>
                                        <path
                                            d="M92,343.72a4.09,4.09,0,0,1-.31,1.61l-2.15-1.23a4.2,4.2,0,0,0,2.43-3.26C91.94,341.77,92,343.63,92,343.72Z"
                                            style="fill: #314A48; transform-origin: 90.7701px 343.085px;"
                                            id="elsyhhrn0ta0p" class="animable"></path>
                                        <g id="elzurhm5s9o9b">
                                            <g style="opacity: 0.3; transform-origin: 90.7701px 343.085px;"
                                                class="animable" id="elotsz9wmu2y">
                                                <path
                                                    d="M92,343.72a4.09,4.09,0,0,1-.31,1.61l-2.15-1.23a4.2,4.2,0,0,0,2.43-3.26C91.94,341.77,92,343.63,92,343.72Z"
                                                    id="elo8unsohi6d" class="animable"
                                                    style="transform-origin: 90.7701px 343.085px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M92,345.52,89.5,344.1A4.18,4.18,0,0,0,92,340.34c0-.12,0-.22,0-.33-.11-1.17-1-2.3-2.74-3.32h0a11.27,11.27,0,0,0-5.7-1.48H83l-.51,0-.44,0-.18,0-.34,0a21.9,21.9,0,0,0-2.77.58c-.44.11-.87.24-1.3.38l-1.79.58-.68.24a21.36,21.36,0,0,1-3.79,1.08,4,4,0,0,1-1.64-.07,3.57,3.57,0,0,1-.76-.31c-1-.56-1.11-1.39.69-2.43a6.26,6.26,0,0,1,5.5-.58L78.75,331a16.32,16.32,0,0,0-11.49.64l-3.17-1.83-4.32,2.5,3.17,1.82c-1.54,1.2-2.37,2.44-2.35,3.64v0c0,1.22.93,2.4,2.81,3.48,4.17,2.41,8.3,1.62,12.07.4,3.51-1.15,6.07-2.5,8.1-1.33,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4,4a14.57,14.57,0,0,0,6.14.94,18,18,0,0,0,6.12-1.49l3.17,1.83,4.28-2.47Z"
                                            style="fill: #314A48; transform-origin: 76.26px 339.095px;"
                                            id="elpw02beflum" class="animable"></path>
                                        <g id="el7cxoae6rcto">
                                            <g style="opacity: 0.3; transform-origin: 76.26px 339.095px;"
                                                class="animable" id="eloq635rrdjeh">
                                                <path
                                                    d="M92,345.52,89.5,344.1A4.18,4.18,0,0,0,92,340.34c0-.12,0-.22,0-.33-.11-1.17-1-2.3-2.74-3.32h0a11.27,11.27,0,0,0-5.7-1.48H83l-.51,0-.44,0-.18,0-.34,0a21.9,21.9,0,0,0-2.77.58c-.44.11-.87.24-1.3.38l-1.79.58-.68.24a21.36,21.36,0,0,1-3.79,1.08,4,4,0,0,1-1.64-.07,3.57,3.57,0,0,1-.76-.31c-1-.56-1.11-1.39.69-2.43a6.26,6.26,0,0,1,5.5-.58L78.75,331a16.32,16.32,0,0,0-11.49.64l-3.17-1.83-4.32,2.5,3.17,1.82c-1.54,1.2-2.37,2.44-2.35,3.64v0c0,1.22.93,2.4,2.81,3.48,4.17,2.41,8.3,1.62,12.07.4,3.51-1.15,6.07-2.5,8.1-1.33,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4,4a14.57,14.57,0,0,0,6.14.94,18,18,0,0,0,6.12-1.49l3.17,1.83,4.28-2.47Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 76.26px 339.095px;"
                                                    id="elzvggg6s2ifb" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="85.21 346.57 85.21 349.95 88.38 351.78 88.38 348.4 85.21 346.57"
                                            style="fill: #314A48; transform-origin: 86.795px 349.175px;"
                                            id="el443817y2p5w" class="animable"></polygon>
                                        <g id="el3wx3s4buh2z">
                                            <polygon
                                                points="85.21 346.57 85.21 349.95 88.38 351.78 88.38 348.4 85.21 346.57"
                                                style="opacity: 0.45; transform-origin: 86.795px 349.175px;"
                                                class="animable" id="elqg83f6gxp3"></polygon>
                                        </g>
                                        <g id="ela01sy2vwu1r">
                                            <polygon
                                                points="59.77 332.28 64.09 329.79 67.26 331.61 64.12 330.29 59.77 332.28"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 63.515px 331.035px;"
                                                class="animable" id="elqbgpo0whqj9"></polygon>
                                        </g>
                                        <g id="elriy10mi03m">
                                            <path
                                                d="M69.44,335.39a6.26,6.26,0,0,1,5.5-.58L78.75,331l-3.81,3.45A5.76,5.76,0,0,0,69.44,335.39Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 74.095px 333.195px;"
                                                class="animable" id="el1akyi1wh0bh"></path>
                                        </g>
                                        <g id="el9ptwb3tk1a">
                                            <path
                                                d="M73,347.12a14.57,14.57,0,0,0,6.14.94,13.76,13.76,0,0,1-5.5-1.1L77,343.1Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 76.07px 345.594px;"
                                                class="animable" id="elrm579f3jfrf"></path>
                                        </g>
                                        <g id="elk8dmhyczre">
                                            <polygon
                                                points="85.21 346.57 88.38 348 92.66 345.93 88.38 348.4 85.21 346.57"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 88.935px 347.165px;"
                                                class="animable" id="el96ysw56s2ps"></polygon>
                                        </g>
                                        <g id="elq47zu7ujrv8">
                                            <path
                                                d="M47.46,325.3c11.24-6.83,28.76-7.51,28.76-7.51-11,0-21.35,2.47-29.09,6.94-7.42,4.28-11.51,9.86-11.51,15.73C35.62,340.46,36,332.24,47.46,325.3Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 55.92px 329.125px;"
                                                class="animable" id="el1t36g63z227"></path>
                                        </g>
                                        <g id="el32mrxxoxwdy">
                                            <path
                                                d="M73,364.89c11.93.53,24.17-1.84,33.29-7.11,9.49-5.47,13.54-12.9,12.17-20,0,0,1.77,11.64-12.89,19.61S73,364.89,73,364.89Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 95.8598px 351.371px;"
                                                class="animable" id="el3lt6m3aa4ec"></path>
                                        </g>
                                        <path
                                            d="M112.22,328.58a27.44,27.44,0,0,0-10.14-9.38c-16.57-9.57-43.45-9.57-60,0a27.52,27.52,0,0,0-10.14,9.38H29.62v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.58,43.46,9.58,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                            style="fill: #314A48; transform-origin: 72.04px 336.534px;"
                                            id="elvvpiwf05x6r" class="animable"></path>
                                        <g id="elzaw4wqu6h1f">
                                            <g style="opacity: 0.25; transform-origin: 72.04px 336.534px;"
                                                class="animable" id="eltzl0t43kw5i">
                                                <path
                                                    d="M112.22,328.58a27.44,27.44,0,0,0-10.14-9.38c-16.57-9.57-43.45-9.57-60,0a27.52,27.52,0,0,0-10.14,9.38H29.62v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.58,43.46,9.58,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                                    id="el1c12tiimujbh" class="animable"
                                                    style="transform-origin: 72.04px 336.534px;"></path>
                                            </g>
                                        </g>
                                        <g id="ellvoyocvmdi">
                                            <path
                                                d="M72.07,312c-10.86,0-21.73,2.39-30,7.17a27.52,27.52,0,0,0-10.14,9.38H29.62v8.67h0c.3,6,4.43,12,12.4,16.61,8.29,4.79,19.16,7.18,30,7.18Z"
                                                style="opacity: 0.1; transform-origin: 50.845px 336.505px;"
                                                class="animable" id="elcenisvq25wm"></path>
                                        </g>
                                        <g id="elchsm8hw6m3p">
                                            <path
                                                d="M48.88,316a48.11,48.11,0,0,0-6.83,3.2,27.52,27.52,0,0,0-10.14,9.38H29.62v8.67h0c.3,6,4.43,12,12.4,16.61a46,46,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 39.25px 336.53px;"
                                                class="animable" id="eli2kxl1a5s4r"></path>
                                        </g>
                                        <g id="elky3cqxb1cz">
                                            <path
                                                d="M95.25,316a47.6,47.6,0,0,1,6.83,3.2,27.44,27.44,0,0,1,10.14,9.38h2.3v8.67h0c-.31,6-4.46,12-12.44,16.61a45.54,45.54,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 104.885px 336.53px;"
                                                class="animable" id="el7r47s3nu7a"></path>
                                        </g>
                                        <path
                                            d="M102.08,311.26c-16.57-9.58-43.45-9.58-60,0s-16.58,25.08,0,34.66,43.46,9.57,60,0S118.66,320.83,102.08,311.26Z"
                                            style="fill: #314A48; transform-origin: 72.0781px 328.588px;"
                                            id="elx46fgzr5vaa" class="animable"></path>
                                        <g id="el18dajrho8i">
                                            <path
                                                d="M102.08,311.26c-16.57-9.58-43.45-9.58-60,0s-16.58,25.08,0,34.66,43.46,9.57,60,0S118.66,320.83,102.08,311.26Z"
                                                style="opacity: 0.2; transform-origin: 72.0781px 328.588px;"
                                                class="animable" id="el1mwlun0d53a"></path>
                                        </g>
                                        <path
                                            d="M91.62,317.6c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.55,4.17c-5.55,2.85-8.61,6.75-8.61,11s3.06,8.13,8.61,11c5.25,2.7,12.19,4.18,19.55,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S97.17,320.45,91.62,317.6Z"
                                            style="fill: #314A48; transform-origin: 72.07px 328.605px;"
                                            id="el60it1ygwc5w" class="animable"></path>
                                        <g id="elhq8elgceaiv">
                                            <path
                                                d="M91.62,317.6c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.55,4.17c-5.55,2.85-8.61,6.75-8.61,11s3.06,8.13,8.61,11c5.25,2.7,12.19,4.18,19.55,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S97.17,320.45,91.62,317.6Z"
                                                style="opacity: 0.4; transform-origin: 72.07px 328.605px;"
                                                class="animable" id="eltzf57adr0w"></path>
                                        </g>
                                        <path
                                            d="M101.16,312.86c-7.74-4.47-18.07-6.94-29.09-6.94S50.72,308.39,43,312.86c-7.43,4.28-11.51,9.86-11.51,15.73S35.55,340,43,344.32c7.74,4.46,18.07,6.93,29.09,6.93s21.35-2.47,29.09-6.93c7.42-4.29,11.51-9.88,11.51-15.73S108.58,317.14,101.16,312.86Zm-9.54,26.71c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.55-4.18c-5.55-2.85-8.61-6.75-8.61-11s3.06-8.14,8.61-11c5.25-2.69,12.19-4.17,19.55-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.75,8.61,11S97.17,336.72,91.62,339.57Z"
                                            style="fill: #314A48; transform-origin: 72.09px 328.585px;"
                                            id="el8dku42q0th8" class="animable"></path>
                                        <g id="elxwyt9h9lzxc">
                                            <path
                                                d="M101.16,312.86c-7.74-4.47-18.07-6.94-29.09-6.94S50.72,308.39,43,312.86c-7.43,4.28-11.51,9.86-11.51,15.73S35.55,340,43,344.32c7.74,4.46,18.07,6.93,29.09,6.93s21.35-2.47,29.09-6.93c7.42-4.29,11.51-9.88,11.51-15.73S108.58,317.14,101.16,312.86Zm-9.54,26.71c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.55-4.18c-5.55-2.85-8.61-6.75-8.61-11s3.06-8.14,8.61-11c5.25-2.69,12.19-4.17,19.55-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.75,8.61,11S97.17,336.72,91.62,339.57Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 72.09px 328.585px;"
                                                class="animable" id="elx1thmu3omnj"></path>
                                        </g>
                                        <g id="elutsnmtpvgd">
                                            <path
                                                d="M91,338.27c10.42-5.35,10.42-14,0-19.37s-27.33-5.36-37.76,0-10.43,14,0,19.37S80.52,343.62,91,338.27Z"
                                                style="opacity: 0.2; transform-origin: 72.1163px 328.583px;"
                                                class="animable" id="el1nmz7tez1z7"></path>
                                        </g>
                                        <path
                                            d="M91,338.27c10.42-5.35,10.42-14,0-19.37s-27.33-5.36-37.76,0-10.43,14,0,19.37S80.52,343.62,91,338.27Z"
                                            style="fill: #314A48; transform-origin: 72.1163px 328.583px;"
                                            id="elmnh59wdoyjn" class="animable"></path>
                                        <g id="elxzu62ghm8ra">
                                            <path
                                                d="M91,338.27c10.42-5.35,10.42-14,0-19.37s-27.33-5.36-37.76,0-10.43,14,0,19.37S80.52,343.62,91,338.27Z"
                                                style="opacity: 0.45; transform-origin: 72.1163px 328.583px;"
                                                class="animable" id="elnet7m76t4r"></path>
                                        </g>
                                        <path
                                            d="M91,322.53c-10.43-5.36-27.33-5.36-37.76,0-4.31,2.21-6.84,5-7.58,7.87.74,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C97.78,327.52,95.26,324.74,91,322.53Z"
                                            style="fill: #314A48; transform-origin: 72.12px 330.396px;"
                                            id="ell04r0ieukwh" class="animable"></path>
                                        <g id="el13zpxnxylti">
                                            <path
                                                d="M91,322.53c-10.43-5.36-27.33-5.36-37.76,0-4.31,2.21-6.84,5-7.58,7.87.74,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C97.78,327.52,95.26,324.74,91,322.53Z"
                                                style="opacity: 0.3; transform-origin: 72.12px 330.396px;"
                                                class="animable" id="els7cl8syjs8h"></path>
                                        </g>
                                        <polygon
                                            points="74.6 319.11 74.6 323.02 73.26 324.43 70.79 327.02 70.79 322.94 74.6 319.11"
                                            style="fill: #314A48; transform-origin: 72.695px 323.065px;"
                                            id="elor6d32sk8b" class="animable"></polygon>
                                        <g id="el0miqnqf95v5">
                                            <polygon
                                                points="74.6 319.11 74.6 323.02 73.26 324.43 70.79 327.02 70.79 322.94 74.6 319.11"
                                                style="opacity: 0.3; transform-origin: 72.695px 323.065px;"
                                                class="animable" id="ela8manfyu7y"></polygon>
                                        </g>
                                        <path
                                            d="M70.79,322.94v2.31a22.32,22.32,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.69,3.69,0,0,1-.77-.32c-1-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,70.79,322.94Z"
                                            style="fill: #314A48; transform-origin: 67.3339px 324.47px;"
                                            id="elu8qpitdvwp" class="animable"></path>
                                        <g id="el5drq8bftyh">
                                            <path
                                                d="M70.79,322.94v2.31a22.32,22.32,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.69,3.69,0,0,1-.77-.32c-1-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,70.79,322.94Z"
                                                style="opacity: 0.45; transform-origin: 67.3339px 324.47px;"
                                                class="animable" id="elgrygewvx7bi"></path>
                                        </g>
                                        <path
                                            d="M88.51,334.06v3.38l-4.28,2.47-3.17-1.83a16.15,16.15,0,0,1-12.26.55v-3.38l1.94-1.93-1.94.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.1-2.81-2.32-2.81-3.56v-3.29l.35-1.44-1.17-.68v-3.38l3.17,1.83c-1.55,1.2-2.37,2.43-2.35,3.63v0c0,1.21.93,2.39,2.81,3.47,4.16,2.41,8.29,1.61,12.07.4,3.51-1.15,6.07-2.49,8.1-1.32,1.26.72.87,1.61-.54,2.42a7,7,0,0,1-6,.35l-4,4a14,14,0,0,0,2.23.65,16.67,16.67,0,0,0,10-1.2l3.17,1.83Z"
                                            style="fill: #314A48; transform-origin: 72.065px 330.16px;"
                                            id="eluwepvfmle1" class="animable"></path>
                                        <g id="el63zgbq2y67l">
                                            <path
                                                d="M56.44,325.87v0c0,1.21.93,2.39,2.81,3.47,4.16,2.41,8.29,1.61,12.07.4,3.51-1.15,6.07-2.49,8.1-1.32,1.26.72.87,1.61-.54,2.42a7,7,0,0,1-6,.35l-4,4a14,14,0,0,0,2.23.65,16.67,16.67,0,0,0,10-1.2l3.17,1.83,4.28-2.47v3.38l-4.28,2.47-3.17-1.83a16.15,16.15,0,0,1-12.26.55v-3.38l1.94-1.93-1.94.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.1-2.81-2.32-2.81-3.56v-3.29l.35-1.44"
                                                style="opacity: 0.2; transform-origin: 72.5px 332.13px;"
                                                class="animable" id="elaucvvx8hf8"></path>
                                        </g>
                                        <g id="elrq8l71qgmi">
                                            <path d="M55.62,320.41v3.38l1.17.68h0a6.79,6.79,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 57.205px 322.44px;"
                                                class="animable" id="elyhw5vezaq"></path>
                                        </g>
                                        <path
                                            d="M87.8,331.85a3.91,3.91,0,0,1-.32,1.61l-2.14-1.23A4.2,4.2,0,0,0,87.77,329C87.78,329.9,87.8,331.76,87.8,331.85Z"
                                            style="fill: #314A48; transform-origin: 86.5702px 331.23px;"
                                            id="el1oy3v5k6rfdi" class="animable"></path>
                                        <g id="eldw9k6q0o5n4">
                                            <g style="opacity: 0.3; transform-origin: 86.5702px 331.23px;"
                                                class="animable" id="el0pr06r3j4knl">
                                                <path
                                                    d="M87.8,331.85a3.91,3.91,0,0,1-.32,1.61l-2.14-1.23A4.2,4.2,0,0,0,87.77,329C87.78,329.9,87.8,331.76,87.8,331.85Z"
                                                    id="elrw85mz1mwdc" class="animable"
                                                    style="transform-origin: 86.5702px 331.23px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M87.79,333.65l-2.45-1.42a4.18,4.18,0,0,0,2.45-3.76c0-.12,0-.22,0-.33-.1-1.16-1-2.3-2.74-3.32h0a11.21,11.21,0,0,0-5.69-1.48H78.8l-.51,0-.45,0-.18,0a2,2,0,0,0-.34.05,20.91,20.91,0,0,0-2.77.57c-.43.11-.87.24-1.29.38-.62.19-1.22.39-1.79.58l-.68.24a21.19,21.19,0,0,1-3.8,1.08,4.16,4.16,0,0,1-1.63-.06,4.31,4.31,0,0,1-.77-.32c-1-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.58l3.81-3.84a16.37,16.37,0,0,0-11.49.64l-3.17-1.82-4.32,2.49,3.17,1.82c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.82,3.48,4.17,2.41,8.29,1.62,12.07.4,3.51-1.15,6.07-2.5,8.09-1.33,1.27.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.06,4a15.66,15.66,0,0,0,2.24.66,14.56,14.56,0,0,0,3.91.28,18,18,0,0,0,6.12-1.49l3.17,1.83,4.27-2.47Z"
                                            style="fill: #314A48; transform-origin: 72.075px 327.11px;"
                                            id="elr6do7dqwuu" class="animable"></path>
                                        <g id="elgchyijzsqw">
                                            <g style="opacity: 0.3; transform-origin: 72.075px 327.11px;"
                                                class="animable" id="elh7t3p6lilki">
                                                <path
                                                    d="M87.79,333.65l-2.45-1.42a4.18,4.18,0,0,0,2.45-3.76c0-.12,0-.22,0-.33-.1-1.16-1-2.3-2.74-3.32h0a11.21,11.21,0,0,0-5.69-1.48H78.8l-.51,0-.45,0-.18,0a2,2,0,0,0-.34.05,20.91,20.91,0,0,0-2.77.57c-.43.11-.87.24-1.29.38-.62.19-1.22.39-1.79.58l-.68.24a21.19,21.19,0,0,1-3.8,1.08,4.16,4.16,0,0,1-1.63-.06,4.31,4.31,0,0,1-.77-.32c-1-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.58l3.81-3.84a16.37,16.37,0,0,0-11.49.64l-3.17-1.82-4.32,2.49,3.17,1.82c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.82,3.48,4.17,2.41,8.29,1.62,12.07.4,3.51-1.15,6.07-2.5,8.09-1.33,1.27.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.06,4a15.66,15.66,0,0,0,2.24.66,14.56,14.56,0,0,0,3.91.28,18,18,0,0,0,6.12-1.49l3.17,1.83,4.27-2.47Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 72.075px 327.11px;"
                                                    id="ele5gto07s4gg" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon points="81.06 334.7 81.06 338.08 84.23 339.91 84.23 336.53 81.06 334.7"
                                            style="fill: #314A48; transform-origin: 82.645px 337.305px;"
                                            id="el7utt1qnrj34" class="animable"></polygon>
                                        <g id="eltamk2vv31db">
                                            <polygon
                                                points="81.06 334.7 81.06 338.08 84.23 339.91 84.23 336.53 81.06 334.7"
                                                style="opacity: 0.45; transform-origin: 82.645px 337.305px;"
                                                class="animable" id="ele5zlhiwjgy9"></polygon>
                                        </g>
                                        <g id="ely1c7qa5ts6">
                                            <polygon
                                                points="55.62 320.41 59.94 317.92 63.11 319.74 59.96 318.43 55.62 320.41"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 59.365px 319.165px;"
                                                class="animable" id="elabtoz42qq64"></polygon>
                                        </g>
                                        <g id="el17ic8d60y35">
                                            <path
                                                d="M65.28,323.52a6.27,6.27,0,0,1,5.51-.58l3.81-3.84-3.81,3.45A5.78,5.78,0,0,0,65.28,323.52Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 69.94px 321.31px;"
                                                class="animable" id="elc7ngci19rwh"></path>
                                        </g>
                                        <g id="el9okkgr0oif5">
                                            <path
                                                d="M68.79,335.25a15.66,15.66,0,0,0,2.24.66,14.56,14.56,0,0,0,3.91.28,13.8,13.8,0,0,1-5.51-1.1l3.42-3.86Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 71.865px 333.725px;"
                                                class="animable" id="elu5d79on6hx"></path>
                                        </g>
                                        <g id="elo4vc17nu6s">
                                            <polygon
                                                points="81.06 334.7 84.23 336.13 88.5 334.06 84.23 336.53 81.06 334.7"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 84.78px 335.295px;"
                                                class="animable" id="elywd1r6co5p"></polygon>
                                        </g>
                                        <g id="ellmaqjxaq2w">
                                            <path
                                                d="M43.3,313.43c11.24-6.83,28.77-7.51,28.77-7.51-11,0-21.35,2.47-29.09,6.94-7.43,4.28-11.51,9.86-11.51,15.73C31.47,328.59,31.87,320.37,43.3,313.43Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 51.77px 317.255px;"
                                                class="animable" id="el9dvv6j65ynv"></path>
                                        </g>
                                        <g id="elg668tvxtqhw">
                                            <path
                                                d="M68.79,353c11.93.53,24.17-1.84,33.29-7.1,9.49-5.48,13.55-12.91,12.18-20.05,0,0,1.77,11.64-12.89,19.61S68.79,353,68.79,353Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 91.6546px 339.461px;"
                                                class="animable" id="elp4dnd2j93v"></path>
                                        </g>
                                    </g>
                                    <g id="freepik--coins--inject-15" class="animable"
                                        style="transform-origin: 142.15px 418.036px;">
                                        <path
                                            d="M183.69,420.07a27.28,27.28,0,0,0-10.14-9.38c-16.57-9.57-43.45-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38h-2.29v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.46,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                            style="fill: #314A48; transform-origin: 143.54px 428.02px;"
                                            id="el5tab2kihlig" class="animable"></path>
                                        <g id="elkndi649fczk">
                                            <g style="opacity: 0.25; transform-origin: 143.54px 428.02px;"
                                                class="animable" id="eljusiq9xmo5">
                                                <path
                                                    d="M183.69,420.07a27.28,27.28,0,0,0-10.14-9.38c-16.57-9.57-43.45-9.57-60,0a27.36,27.36,0,0,0-10.14,9.38h-2.29v8.67h0c.3,6,4.43,12,12.4,16.61,16.58,9.57,43.46,9.57,60,0,8-4.6,12.13-10.58,12.44-16.61h0v-8.67Z"
                                                    id="el4ivseufxqvx" class="animable"
                                                    style="transform-origin: 143.54px 428.02px;"></path>
                                            </g>
                                        </g>
                                        <g id="elzc56sbnkrge">
                                            <path
                                                d="M143.54,403.51c-10.86,0-21.73,2.39-30,7.18a27.36,27.36,0,0,0-10.14,9.38h-2.29v8.67h0c.3,6,4.43,12,12.4,16.61,8.29,4.79,19.16,7.18,30,7.18Z"
                                                style="opacity: 0.1; transform-origin: 122.325px 428.02px;"
                                                class="animable" id="elu8m1u5hyjwb"></path>
                                        </g>
                                        <g id="elk276bqvlxi">
                                            <path
                                                d="M120.35,407.49a47,47,0,0,0-6.83,3.2,27.36,27.36,0,0,0-10.14,9.38h-2.29v8.67h0c.3,6,4.43,12,12.4,16.61a46,46,0,0,0,6.83,3.2Z"
                                                style="opacity: 0.1; transform-origin: 110.72px 428.02px;"
                                                class="animable" id="elw3t283quam"></path>
                                        </g>
                                        <g id="el806c3ffipn8">
                                            <path
                                                d="M166.72,407.49a46.55,46.55,0,0,1,6.83,3.2,27.28,27.28,0,0,1,10.14,9.38H186v8.67h0c-.31,6-4.46,12-12.44,16.61a45.54,45.54,0,0,1-6.83,3.2Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 176.36px 428.02px;"
                                                class="animable" id="elgnh2it3p9s"></path>
                                        </g>
                                        <path
                                            d="M173.55,402.74c-16.57-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.46,9.57,60,0S190.13,412.31,173.55,402.74Z"
                                            style="fill: #314A48; transform-origin: 143.548px 420.07px;"
                                            id="elqfrou3i0ub" class="animable"></path>
                                        <g id="el6a30lriqvoh">
                                            <path
                                                d="M173.55,402.74c-16.57-9.57-43.45-9.57-60,0s-16.58,25.09,0,34.66,43.46,9.57,60,0S190.13,412.31,173.55,402.74Z"
                                                style="opacity: 0.2; transform-origin: 143.548px 420.07px;"
                                                class="animable" id="elt4z9lgfjny"></path>
                                        </g>
                                        <path
                                            d="M163.09,409.08c-5.26-2.68-12.19-4.17-19.55-4.17s-14.3,1.49-19.55,4.17c-5.55,2.85-8.61,6.76-8.61,11s3.06,8.14,8.61,11c5.25,2.69,12.19,4.18,19.55,4.18s14.29-1.49,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S168.64,411.93,163.09,409.08Z"
                                            style="fill: #314A48; transform-origin: 143.54px 420.085px;"
                                            id="elen9ipm7qici" class="animable"></path>
                                        <g id="eldrxaqizanht">
                                            <path
                                                d="M163.09,409.08c-5.26-2.68-12.19-4.17-19.55-4.17s-14.3,1.49-19.55,4.17c-5.55,2.85-8.61,6.76-8.61,11s3.06,8.14,8.61,11c5.25,2.69,12.19,4.18,19.55,4.18s14.29-1.49,19.55-4.18c5.55-2.85,8.61-6.75,8.61-11S168.64,411.93,163.09,409.08Z"
                                                style="opacity: 0.4; transform-origin: 143.54px 420.085px;"
                                                class="animable" id="elmrbcmvrbx5k"></path>
                                        </g>
                                        <path
                                            d="M172.63,404.34c-7.74-4.47-18.07-6.93-29.09-6.93s-21.35,2.46-29.09,6.93c-7.43,4.29-11.51,9.87-11.51,15.73s4.08,11.45,11.51,15.74c7.74,4.46,18.07,6.92,29.09,6.92s21.35-2.46,29.09-6.92c7.42-4.29,11.51-9.88,11.51-15.74S180.05,408.63,172.63,404.34Zm-9.54,26.72c-5.26,2.69-12.19,4.18-19.55,4.18s-14.3-1.49-19.55-4.18c-5.55-2.85-8.61-6.75-8.61-11s3.06-8.14,8.61-11c5.25-2.68,12.19-4.17,19.55-4.17s14.29,1.49,19.55,4.17c5.55,2.85,8.61,6.76,8.61,11S168.64,428.21,163.09,431.06Z"
                                            style="fill: #314A48; transform-origin: 143.54px 420.07px;"
                                            id="elvl8hifqg57" class="animable"></path>
                                        <g id="elji7z9ljlo6m">
                                            <path
                                                d="M172.63,404.34c-7.74-4.47-18.07-6.93-29.09-6.93s-21.35,2.46-29.09,6.93c-7.43,4.29-11.51,9.87-11.51,15.73s4.08,11.45,11.51,15.74c7.74,4.46,18.07,6.92,29.09,6.92s21.35-2.46,29.09-6.92c7.42-4.29,11.51-9.88,11.51-15.74S180.05,408.63,172.63,404.34Zm-9.54,26.72c-5.26,2.69-12.19,4.18-19.55,4.18s-14.3-1.49-19.55-4.18c-5.55-2.85-8.61-6.75-8.61-11s3.06-8.14,8.61-11c5.25-2.68,12.19-4.17,19.55-4.17s14.29,1.49,19.55,4.17c5.55,2.85,8.61,6.76,8.61,11S168.64,428.21,163.09,431.06Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 143.54px 420.07px;"
                                                class="animable" id="elcvgxpjqcyf"></path>
                                        </g>
                                        <g id="elaj57mrmlnep">
                                            <path
                                                d="M162.42,429.76c10.42-5.35,10.42-14,0-19.38s-27.33-5.35-37.76,0-10.43,14,0,19.38S152,435.11,162.42,429.76Z"
                                                style="opacity: 0.2; transform-origin: 143.536px 420.07px;"
                                                class="animable" id="eleht8gxoeozb"></path>
                                        </g>
                                        <path
                                            d="M162.42,429.76c10.42-5.35,10.42-14,0-19.38s-27.33-5.35-37.76,0-10.43,14,0,19.38S152,435.11,162.42,429.76Z"
                                            style="fill: #314A48; transform-origin: 143.536px 420.07px;"
                                            id="ela9y7o9qymz7" class="animable"></path>
                                        <g id="el7a0xg4wh05t">
                                            <path
                                                d="M162.42,429.76c10.42-5.35,10.42-14,0-19.38s-27.33-5.35-37.76,0-10.43,14,0,19.38S152,435.11,162.42,429.76Z"
                                                style="opacity: 0.45; transform-origin: 143.536px 420.07px;"
                                                class="animable" id="elja7xi4jopw9"></path>
                                        </g>
                                        <path
                                            d="M162.42,414c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.21-6.84,5-7.58,7.88.74,2.87,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C169.25,419,166.73,416.22,162.42,414Z"
                                            style="fill: #314A48; transform-origin: 143.54px 421.875px;"
                                            id="elzpisl8bv5go" class="animable"></path>
                                        <g id="elkte1viqyej">
                                            <path
                                                d="M162.42,414c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.21-6.84,5-7.58,7.88.74,2.87,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C169.25,419,166.73,416.22,162.42,414Z"
                                                style="opacity: 0.3; transform-origin: 143.54px 421.875px;"
                                                class="animable" id="el8tegx25j8vc"></path>
                                        </g>
                                        <polygon
                                            points="146.07 410.59 146.07 414.51 144.73 415.92 142.26 418.5 142.26 414.43 146.07 410.59"
                                            style="fill: #314A48; transform-origin: 144.165px 414.545px;"
                                            id="elu8wffcn3ek" class="animable"></polygon>
                                        <g id="elby75up4go6">
                                            <polygon
                                                points="146.07 410.59 146.07 414.51 144.73 415.92 142.26 418.5 142.26 414.43 146.07 410.59"
                                                style="opacity: 0.3; transform-origin: 144.165px 414.545px;"
                                                class="animable" id="elgpmxdpd7f"></polygon>
                                        </g>
                                        <path
                                            d="M142.26,414.43v2.31a22.32,22.32,0,0,1-3.8,1.08,4.37,4.37,0,0,1-1.63-.06,4.31,4.31,0,0,1-.77-.32c-.95-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,142.26,414.43Z"
                                            style="fill: #314A48; transform-origin: 138.814px 415.957px;"
                                            id="elx1t0968iva" class="animable"></path>
                                        <g id="elydkcn5t98so">
                                            <path
                                                d="M142.26,414.43v2.31a22.32,22.32,0,0,1-3.8,1.08,4.37,4.37,0,0,1-1.63-.06,4.31,4.31,0,0,1-.77-.32c-.95-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,142.26,414.43Z"
                                                style="opacity: 0.45; transform-origin: 138.814px 415.957px;"
                                                class="animable" id="elavegqlkgjhh"></path>
                                        </g>
                                        <path
                                            d="M160,425.55v3.38l-4.28,2.47-3.17-1.84a16.1,16.1,0,0,1-12.26.56v-3.39l1.94-1.92-1.94.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.11-2.81-2.32-2.81-3.57v-3.29l.35-1.44-1.17-.68v-3.38l3.17,1.83c-1.55,1.2-2.37,2.44-2.35,3.63v0c0,1.22.93,2.4,2.81,3.48,4.16,2.41,8.29,1.61,12.07.4,3.51-1.15,6.07-2.5,8.1-1.33,1.26.73.87,1.61-.54,2.43a7,7,0,0,1-6,.35l-4,4a14,14,0,0,0,2.23.66,16.67,16.67,0,0,0,10-1.2L155.7,428Z"
                                            style="fill: #314A48; transform-origin: 143.555px 421.645px;"
                                            id="el56e0wb77ddk" class="animable"></path>
                                        <g id="elnqxerlphaic">
                                            <path
                                                d="M127.91,417.35v0c0,1.22.93,2.4,2.81,3.48,4.16,2.41,8.29,1.61,12.07.4,3.51-1.15,6.07-2.5,8.1-1.33,1.26.73.87,1.61-.54,2.43a7,7,0,0,1-6,.35l-4,4a14,14,0,0,0,2.23.66,16.67,16.67,0,0,0,10-1.2L155.7,428l4.28-2.47v3.38l-4.28,2.47-3.17-1.84a16.1,16.1,0,0,1-12.26.56v-3.39l1.94-1.92-1.94.53a12.49,12.49,0,0,1-9.55-1.09c-1.92-1.11-2.81-2.32-2.81-3.57v-3.29l.35-1.44"
                                                style="opacity: 0.2; transform-origin: 143.945px 423.655px;"
                                                class="animable" id="el0r8p2joukfi"></path>
                                        </g>
                                        <g id="elu42uas1dshk">
                                            <path d="M127.09,411.89v3.38l1.17.68h0a6.89,6.89,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 128.675px 413.92px;"
                                                class="animable" id="el4tgtufra1pl"></path>
                                        </g>
                                        <path
                                            d="M159.27,423.34A3.91,3.91,0,0,1,159,425l-2.14-1.23a4.22,4.22,0,0,0,2.43-3.26C159.25,421.39,159.27,423.24,159.27,423.34Z"
                                            style="fill: #314A48; transform-origin: 158.075px 422.755px;"
                                            id="elpzl3izp5vwb" class="animable"></path>
                                        <g id="elz68f084lmx">
                                            <g style="opacity: 0.3; transform-origin: 158.075px 422.755px;"
                                                class="animable" id="eldpigmx5bbop">
                                                <path
                                                    d="M159.27,423.34A3.91,3.91,0,0,1,159,425l-2.14-1.23a4.22,4.22,0,0,0,2.43-3.26C159.25,421.39,159.27,423.24,159.27,423.34Z"
                                                    id="elo266ag14w8" class="animable"
                                                    style="transform-origin: 158.075px 422.755px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M159.26,425.14l-2.45-1.43a4.16,4.16,0,0,0,2.45-3.75c0-.12,0-.22,0-.33-.1-1.17-1-2.3-2.74-3.32h0a11.22,11.22,0,0,0-5.69-1.49h-.54l-.51,0-.45,0-.18,0-.34,0a24.24,24.24,0,0,0-2.77.57c-.43.12-.87.25-1.29.39-.62.19-1.22.39-1.79.58l-.68.23a20.55,20.55,0,0,1-3.8,1.09,4,4,0,0,1-1.63-.07,3.22,3.22,0,0,1-.77-.32c-.95-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.57l3.81-3.84a16.27,16.27,0,0,0-11.49.64l-3.17-1.83-4.32,2.5,3.17,1.82c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.82,3.48,4.16,2.41,8.29,1.61,12.07.39,3.51-1.14,6.07-2.49,8.09-1.32,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.06,4a15.58,15.58,0,0,0,2.24.65,15,15,0,0,0,3.91.29,18.27,18.27,0,0,0,6.12-1.49L155.7,428l4.27-2.47Z"
                                            style="fill: #314A48; transform-origin: 143.54px 418.63px;"
                                            id="el6jtjlso2wv" class="animable"></path>
                                        <g id="elbkddsh1nl1">
                                            <g style="opacity: 0.3; transform-origin: 143.54px 418.63px;"
                                                class="animable" id="el7bbkzxglao9">
                                                <path
                                                    d="M159.26,425.14l-2.45-1.43a4.16,4.16,0,0,0,2.45-3.75c0-.12,0-.22,0-.33-.1-1.17-1-2.3-2.74-3.32h0a11.22,11.22,0,0,0-5.69-1.49h-.54l-.51,0-.45,0-.18,0-.34,0a24.24,24.24,0,0,0-2.77.57c-.43.12-.87.25-1.29.39-.62.19-1.22.39-1.79.58l-.68.23a20.55,20.55,0,0,1-3.8,1.09,4,4,0,0,1-1.63-.07,3.22,3.22,0,0,1-.77-.32c-.95-.55-1.1-1.39.69-2.43a6.27,6.27,0,0,1,5.51-.57l3.81-3.84a16.27,16.27,0,0,0-11.49.64l-3.17-1.83-4.32,2.5,3.17,1.82c-1.55,1.2-2.38,2.44-2.36,3.64v0c0,1.22.94,2.4,2.82,3.48,4.16,2.41,8.29,1.61,12.07.39,3.51-1.14,6.07-2.49,8.09-1.32,1.26.73.88,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.06,4a15.58,15.58,0,0,0,2.24.65,15,15,0,0,0,3.91.29,18.27,18.27,0,0,0,6.12-1.49L155.7,428l4.27-2.47Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 143.54px 418.63px;"
                                                    id="el3oi7rn5xee8" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="152.53 426.19 152.53 429.56 155.7 431.39 155.7 428.02 152.53 426.19"
                                            style="fill: #314A48; transform-origin: 154.115px 428.79px;"
                                            id="eld3u6iks4qy8" class="animable"></polygon>
                                        <g id="el9hahh8a8wwr">
                                            <polygon
                                                points="152.53 426.19 152.53 429.56 155.7 431.39 155.7 428.02 152.53 426.19"
                                                style="opacity: 0.45; transform-origin: 154.115px 428.79px;"
                                                class="animable" id="elgd24o4tm738"></polygon>
                                        </g>
                                        <g id="elhwf98xxryyt">
                                            <polygon
                                                points="127.09 411.9 131.41 409.4 134.58 411.23 131.43 409.91 127.09 411.9"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 130.835px 410.65px;"
                                                class="animable" id="elor83psxw1x"></polygon>
                                        </g>
                                        <g id="el6zw106mrai">
                                            <path
                                                d="M136.75,415a6.27,6.27,0,0,1,5.51-.57l3.81-3.84L142.26,414A5.79,5.79,0,0,0,136.75,415Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 141.41px 412.795px;"
                                                class="animable" id="el555t8bt7za3"></path>
                                        </g>
                                        <g id="elm1n6uu2gzve">
                                            <path
                                                d="M140.26,426.74a15.58,15.58,0,0,0,2.24.65,15,15,0,0,0,3.91.29,13.8,13.8,0,0,1-5.51-1.1l3.42-3.86Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 143.335px 425.212px;"
                                                class="animable" id="el7n7v0s3ssz7"></path>
                                        </g>
                                        <g id="elp0vonxgu009">
                                            <polygon
                                                points="152.53 426.19 155.7 427.62 159.97 425.55 155.7 428.02 152.53 426.19"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 156.25px 426.785px;"
                                                class="animable" id="elvqrpt51119"></polygon>
                                        </g>
                                        <g id="elj38p3dylzro">
                                            <path
                                                d="M114.77,404.91c11.24-6.82,28.77-7.5,28.77-7.5-11,0-21.35,2.46-29.09,6.93-7.43,4.29-11.51,9.87-11.51,15.73C102.94,420.07,103.34,411.86,114.77,404.91Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 123.24px 408.74px;"
                                                class="animable" id="elf16zfchffh"></path>
                                        </g>
                                        <g id="elw5icbufzksl">
                                            <path
                                                d="M140.26,444.51c11.93.53,24.17-1.84,33.29-7.11,9.49-5.47,13.55-12.9,12.18-20,0,0,1.77,11.64-12.89,19.61S140.26,444.51,140.26,444.51Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 163.125px 430.991px;"
                                                class="animable" id="eloa9ed30u2j"></path>
                                        </g>
                                        <path
                                            d="M180.91,408a27.42,27.42,0,0,0-10.14-9.37c-16.58-9.58-43.46-9.58-60,0A27.42,27.42,0,0,0,100.59,408H98.3v8.68h0c.31,6,4.43,12,12.4,16.61,16.58,9.57,43.46,9.57,60,0,8-4.61,12.12-10.58,12.43-16.61h0V408Z"
                                            style="fill: #314A48; transform-origin: 140.715px 415.956px;"
                                            id="elbybn9b43078" class="animable"></path>
                                        <g id="eld1mmthj0wug">
                                            <g style="opacity: 0.25; transform-origin: 140.715px 415.956px;"
                                                class="animable" id="el2khnrf8mqrv">
                                                <path
                                                    d="M180.91,408a27.42,27.42,0,0,0-10.14-9.37c-16.58-9.58-43.46-9.58-60,0A27.42,27.42,0,0,0,100.59,408H98.3v8.68h0c.31,6,4.43,12,12.4,16.61,16.58,9.57,43.46,9.57,60,0,8-4.61,12.12-10.58,12.43-16.61h0V408Z"
                                                    id="el4ger2mly3e1" class="animable"
                                                    style="transform-origin: 140.715px 415.956px;"></path>
                                            </g>
                                        </g>
                                        <g id="elvaoi0vjx3ei">
                                            <path
                                                d="M140.75,391.49c-10.86,0-21.73,2.39-30,7.18A27.42,27.42,0,0,0,100.59,408H98.3v8.68h0c.31,6,4.43,12,12.4,16.61,8.29,4.78,19.16,7.17,30,7.17Z"
                                                style="opacity: 0.1; transform-origin: 119.525px 415.975px;"
                                                class="animable" id="elgjzp4x64g8p"></path>
                                        </g>
                                        <g id="el5od3b1bpuq">
                                            <path
                                                d="M117.57,395.47a46.12,46.12,0,0,0-6.84,3.2A27.42,27.42,0,0,0,100.59,408H98.3v8.68h0c.31,6,4.43,12,12.4,16.61a47.12,47.12,0,0,0,6.84,3.19Z"
                                                style="opacity: 0.1; transform-origin: 107.935px 415.975px;"
                                                class="animable" id="elxt5f2875at9"></path>
                                        </g>
                                        <g id="elfm4flanovwo">
                                            <path
                                                d="M163.93,395.47a46.12,46.12,0,0,1,6.84,3.2A27.42,27.42,0,0,1,180.91,408h2.29v8.68h0c-.31,6-4.46,12-12.43,16.61a47.12,47.12,0,0,1-6.84,3.19Z"
                                                style="fill: #314A48; opacity: 0.5; transform-origin: 173.565px 415.975px;"
                                                class="animable" id="ele9byxezz8fg"></path>
                                        </g>
                                        <path
                                            d="M170.77,390.72c-16.58-9.57-43.46-9.57-60,0s-16.57,25.09,0,34.66,43.46,9.57,60,0S187.34,400.29,170.77,390.72Z"
                                            style="fill: #314A48; transform-origin: 140.77px 408.05px;"
                                            id="elhnb8p6g4rek" class="animable"></path>
                                        <g id="el1wwtjzfki6c">
                                            <path
                                                d="M170.77,390.72c-16.58-9.57-43.46-9.57-60,0s-16.57,25.09,0,34.66,43.46,9.57,60,0S187.34,400.29,170.77,390.72Z"
                                                style="opacity: 0.2; transform-origin: 140.77px 408.05px;"
                                                class="animable" id="el2cfx4wa76hj"></path>
                                        </g>
                                        <path
                                            d="M160.3,397.06c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.85-8.62,6.75-8.62,11s3.06,8.13,8.62,11c5.24,2.7,12.18,4.18,19.54,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.74,8.61-11S165.85,399.91,160.3,397.06Z"
                                            style="fill: #314A48; transform-origin: 140.75px 408.065px;"
                                            id="eldrhg2sc13js" class="animable"></path>
                                        <g id="el7ivdes68gv4">
                                            <path
                                                d="M160.3,397.06c-5.25-2.69-12.19-4.17-19.55-4.17s-14.3,1.48-19.54,4.17c-5.56,2.85-8.62,6.75-8.62,11s3.06,8.13,8.62,11c5.24,2.7,12.18,4.18,19.54,4.18s14.3-1.48,19.55-4.18c5.55-2.85,8.61-6.74,8.61-11S165.85,399.91,160.3,397.06Z"
                                                style="opacity: 0.4; transform-origin: 140.75px 408.065px;"
                                                class="animable" id="elcw26rm3mxai"></path>
                                        </g>
                                        <path
                                            d="M169.84,392.32c-7.74-4.47-18.07-6.94-29.09-6.94s-21.35,2.47-29.09,6.94c-7.43,4.28-11.51,9.86-11.51,15.73s4.08,11.44,11.51,15.73c7.74,4.46,18.07,6.93,29.09,6.93s21.35-2.47,29.09-6.93c7.42-4.29,11.51-9.87,11.51-15.73S177.26,396.6,169.84,392.32ZM160.3,419c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.54-4.18c-5.56-2.85-8.62-6.74-8.62-11s3.06-8.14,8.62-11c5.24-2.69,12.18-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.75,8.61,11S165.85,416.18,160.3,419Z"
                                            style="fill: #314A48; transform-origin: 140.75px 408.045px;"
                                            id="eld9rw6nthem" class="animable"></path>
                                        <g id="elx0rkjxnad9">
                                            <path
                                                d="M169.84,392.32c-7.74-4.47-18.07-6.94-29.09-6.94s-21.35,2.47-29.09,6.94c-7.43,4.28-11.51,9.86-11.51,15.73s4.08,11.44,11.51,15.73c7.74,4.46,18.07,6.93,29.09,6.93s21.35-2.47,29.09-6.93c7.42-4.29,11.51-9.87,11.51-15.73S177.26,396.6,169.84,392.32ZM160.3,419c-5.25,2.7-12.19,4.18-19.55,4.18s-14.3-1.48-19.54-4.18c-5.56-2.85-8.62-6.74-8.62-11s3.06-8.14,8.62-11c5.24-2.69,12.18-4.17,19.54-4.17s14.3,1.48,19.55,4.17c5.55,2.85,8.61,6.75,8.61,11S165.85,416.18,160.3,419Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 140.75px 408.045px;"
                                                class="animable" id="elk0tc6u4qim"></path>
                                        </g>
                                        <g id="elo6ln08ruwx">
                                            <path
                                                d="M159.63,417.73c10.43-5.35,10.43-14,0-19.37s-27.33-5.35-37.76,0-10.43,14,0,19.37S149.2,423.08,159.63,417.73Z"
                                                style="opacity: 0.2; transform-origin: 140.75px 408.045px;"
                                                class="animable" id="eleeb3kdyp8qo"></path>
                                        </g>
                                        <path
                                            d="M159.63,417.73c10.43-5.35,10.43-14,0-19.37s-27.33-5.35-37.76,0-10.43,14,0,19.37S149.2,423.08,159.63,417.73Z"
                                            style="fill: #314A48; transform-origin: 140.75px 408.045px;"
                                            id="elu2a346bhsx7" class="animable"></path>
                                        <g id="elx38fdl7wjgc">
                                            <path
                                                d="M159.63,417.73c10.43-5.35,10.43-14,0-19.37s-27.33-5.35-37.76,0-10.43,14,0,19.37S149.2,423.08,159.63,417.73Z"
                                                style="opacity: 0.45; transform-origin: 140.75px 408.045px;"
                                                class="animable" id="eli6a6nzbofe"></path>
                                        </g>
                                        <path
                                            d="M159.63,402c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.21-6.83,5-7.58,7.87.75,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C166.46,407,163.94,404.2,159.63,402Z"
                                            style="fill: #314A48; transform-origin: 140.75px 409.87px;"
                                            id="elcm6frlnpnht" class="animable"></path>
                                        <g id="elm9a9anipj0n">
                                            <path
                                                d="M159.63,402c-10.43-5.35-27.33-5.35-37.76,0-4.31,2.21-6.83,5-7.58,7.87.75,2.88,3.27,5.66,7.58,7.87,10.43,5.35,27.33,5.35,37.76,0,4.31-2.21,6.83-5,7.58-7.87C166.46,407,163.94,404.2,159.63,402Z"
                                                style="opacity: 0.3; transform-origin: 140.75px 409.87px;"
                                                class="animable" id="eltoahsjmta7h"></path>
                                        </g>
                                        <polygon
                                            points="143.28 398.57 143.28 402.48 141.94 403.89 139.47 406.48 139.47 402.41 143.28 398.57"
                                            style="fill: #314A48; transform-origin: 141.375px 402.525px;"
                                            id="el3j00ewp3l1y" class="animable"></polygon>
                                        <g id="elrp3nqt29jof">
                                            <polygon
                                                points="143.28 398.57 143.28 402.48 141.94 403.89 139.47 406.48 139.47 402.41 143.28 398.57"
                                                style="opacity: 0.3; transform-origin: 141.375px 402.525px;"
                                                class="animable" id="elsmppjl8vpgf"></polygon>
                                        </g>
                                        <path
                                            d="M139.47,402.4v2.31a21.42,21.42,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.4,3.4,0,0,1-.77-.32c-.94-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,139.47,402.4Z"
                                            style="fill: #314A48; transform-origin: 136.026px 403.93px;"
                                            id="el2k9wfyo4ijy" class="animable"></path>
                                        <g id="elq47smm9i0o">
                                            <path
                                                d="M139.47,402.4v2.31a21.42,21.42,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.4,3.4,0,0,1-.77-.32c-.94-.56-1.1-1.4.7-2.43A6.25,6.25,0,0,1,139.47,402.4Z"
                                                style="opacity: 0.45; transform-origin: 136.026px 403.93px;"
                                                class="animable" id="els2e036nyfps"></path>
                                        </g>
                                        <path
                                            d="M157.19,413.52v3.38l-4.28,2.47-3.17-1.83a16.1,16.1,0,0,1-12.26.55v-3.38l2-1.93-2,.54a12.54,12.54,0,0,1-9.55-1.1c-1.92-1.1-2.81-2.32-2.81-3.56v-3.29l.35-1.44-1.17-.68v-3.38l3.17,1.83c-1.55,1.2-2.37,2.43-2.35,3.63v0c0,1.21.94,2.39,2.81,3.47,4.16,2.41,8.29,1.62,12.07.4,3.51-1.14,6.07-2.49,8.1-1.32,1.26.73.88,1.61-.54,2.43a7.05,7.05,0,0,1-6,.35l-4.05,4a14.93,14.93,0,0,0,2.24.66,16.73,16.73,0,0,0,10-1.21l3.17,1.83Z"
                                            style="fill: #314A48; transform-origin: 140.745px 409.62px;"
                                            id="el8ghzewoxz5n" class="animable"></path>
                                        <g id="elzr6qtccltn">
                                            <path
                                                d="M125.12,405.33v0c0,1.21.94,2.39,2.81,3.47,4.16,2.41,8.29,1.62,12.07.4,3.51-1.14,6.07-2.49,8.1-1.32,1.26.73.88,1.61-.54,2.43a7.05,7.05,0,0,1-6,.35l-4.05,4a14.93,14.93,0,0,0,2.24.66,16.73,16.73,0,0,0,10-1.21l3.17,1.83,4.28-2.47v3.38l-4.28,2.47-3.17-1.83a16.1,16.1,0,0,1-12.26.55v-3.38l2-1.93-2,.54a12.54,12.54,0,0,1-9.55-1.1c-1.92-1.1-2.81-2.32-2.81-3.56v-3.29l.35-1.44"
                                                style="opacity: 0.2; transform-origin: 141.16px 411.6px;"
                                                class="animable" id="elvzej85oz1nj"></path>
                                        </g>
                                        <g id="elu22ppvsbgfb">
                                            <path d="M124.3,399.87v3.38l1.17.68h0a6.91,6.91,0,0,1,2-2.24Z"
                                                style="opacity: 0.45; transform-origin: 125.885px 401.9px;"
                                                class="animable" id="elj49865vcldf"></path>
                                        </g>
                                        <path
                                            d="M156.48,411.31a3.91,3.91,0,0,1-.32,1.61L154,411.69a4.2,4.2,0,0,0,2.43-3.25C156.47,409.36,156.48,411.22,156.48,411.31Z"
                                            style="fill: #314A48; transform-origin: 155.24px 410.68px;"
                                            id="eltmnukd0idq" class="animable"></path>
                                        <g id="eluetypzpa78j">
                                            <g style="opacity: 0.3; transform-origin: 155.24px 410.68px;"
                                                class="animable" id="eltyy62hvy4o">
                                                <path
                                                    d="M156.48,411.31a3.91,3.91,0,0,1-.32,1.61L154,411.69a4.2,4.2,0,0,0,2.43-3.25C156.47,409.36,156.48,411.22,156.48,411.31Z"
                                                    id="elp03bfpr3ejm" class="animable"
                                                    style="transform-origin: 155.24px 410.68px;"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M156.48,413.11,154,411.69a4.18,4.18,0,0,0,2.45-3.76c0-.11,0-.22,0-.33-.1-1.16-1-2.3-2.73-3.32h0a11.27,11.27,0,0,0-5.7-1.48h-.54l-.51,0-.45,0-.18,0a1.75,1.75,0,0,0-.33.05,21.9,21.9,0,0,0-2.77.57c-.44.12-.87.24-1.3.38l-1.79.58-.68.24a21.19,21.19,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.4,3.4,0,0,1-.76-.32c-1-.55-1.11-1.39.68-2.43a6.27,6.27,0,0,1,5.51-.57l3.81-3.84a16.32,16.32,0,0,0-11.49.63l-3.17-1.82-4.32,2.49,3.17,1.83c-1.55,1.19-2.37,2.44-2.35,3.63v0c0,1.22.93,2.39,2.81,3.48,4.17,2.4,8.29,1.61,12.07.39,3.51-1.15,6.07-2.5,8.09-1.33,1.27.73.89,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.05,4a15.17,15.17,0,0,0,2.23.66,15,15,0,0,0,3.91.29,18.28,18.28,0,0,0,6.12-1.5l3.17,1.84,4.28-2.48Z"
                                            style="fill: #314A48; transform-origin: 140.76px 406.57px;"
                                            id="elwasfe486v3" class="animable"></path>
                                        <g id="el7zcq48gby1c">
                                            <g style="opacity: 0.3; transform-origin: 140.76px 406.57px;"
                                                class="animable" id="elqiliy60a7zc">
                                                <path
                                                    d="M156.48,413.11,154,411.69a4.18,4.18,0,0,0,2.45-3.76c0-.11,0-.22,0-.33-.1-1.16-1-2.3-2.73-3.32h0a11.27,11.27,0,0,0-5.7-1.48h-.54l-.51,0-.45,0-.18,0a1.75,1.75,0,0,0-.33.05,21.9,21.9,0,0,0-2.77.57c-.44.12-.87.24-1.3.38l-1.79.58-.68.24a21.19,21.19,0,0,1-3.8,1.08,4,4,0,0,1-1.63-.06,3.4,3.4,0,0,1-.76-.32c-1-.55-1.11-1.39.68-2.43a6.27,6.27,0,0,1,5.51-.57l3.81-3.84a16.32,16.32,0,0,0-11.49.63l-3.17-1.82-4.32,2.49,3.17,1.83c-1.55,1.19-2.37,2.44-2.35,3.63v0c0,1.22.93,2.39,2.81,3.48,4.17,2.4,8.29,1.61,12.07.39,3.51-1.15,6.07-2.5,8.09-1.33,1.27.73.89,1.61-.53,2.43a7,7,0,0,1-6,.35l-4.05,4a15.17,15.17,0,0,0,2.23.66,15,15,0,0,0,3.91.29,18.28,18.28,0,0,0,6.12-1.5l3.17,1.84,4.28-2.48Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 140.76px 406.57px;"
                                                    id="eldae36i2j6l9" class="animable"></path>
                                            </g>
                                        </g>
                                        <polygon
                                            points="149.74 414.16 149.74 417.54 152.91 419.37 152.91 415.99 149.74 414.16"
                                            style="fill: #314A48; transform-origin: 151.325px 416.765px;"
                                            id="elhzb7evdjwpe" class="animable"></polygon>
                                        <g id="el6sdtmxjg3de">
                                            <polygon
                                                points="149.74 414.16 149.74 417.54 152.91 419.37 152.91 415.99 149.74 414.16"
                                                style="opacity: 0.45; transform-origin: 151.325px 416.765px;"
                                                class="animable" id="ely8u873l5qq"></polygon>
                                        </g>
                                        <g id="elyqk0pu0iqy8">
                                            <polygon
                                                points="124.3 399.87 128.62 397.38 131.79 399.2 128.65 397.89 124.3 399.87"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 128.045px 398.625px;"
                                                class="animable" id="el8pjxmbs2864"></polygon>
                                        </g>
                                        <g id="el1mhx2fvp8k8">
                                            <path
                                                d="M134,403a6.27,6.27,0,0,1,5.51-.57l3.81-3.84L139.47,402A5.78,5.78,0,0,0,134,403Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 138.66px 400.795px;"
                                                class="animable" id="elkf48f05a7m"></path>
                                        </g>
                                        <g id="el3h1yz2tgdcl">
                                            <path
                                                d="M137.48,414.71a15.17,15.17,0,0,0,2.23.66,15,15,0,0,0,3.91.29,13.64,13.64,0,0,1-5.5-1.11l3.41-3.86Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 140.55px 413.187px;"
                                                class="animable" id="elpgxawykse2i"></path>
                                        </g>
                                        <g id="elu6y5s0t35v">
                                            <polygon
                                                points="149.74 414.16 152.91 415.59 157.19 413.52 152.91 415.99 149.74 414.16"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 153.465px 414.755px;"
                                                class="animable" id="elbm5nxb2d9pi"></polygon>
                                        </g>
                                        <g id="elu7zh5pp902r">
                                            <path
                                                d="M112,392.89c11.23-6.83,28.76-7.51,28.76-7.51-11,0-21.35,2.47-29.09,6.94-7.43,4.28-11.51,9.86-11.51,15.73C100.15,408.05,100.55,399.83,112,392.89Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 120.46px 396.715px;"
                                                class="animable" id="el0sey413togc"></path>
                                        </g>
                                        <g id="el5k5n07grksm">
                                            <path
                                                d="M137.47,432.48c11.93.53,24.17-1.84,33.3-7.1,9.48-5.48,13.54-12.91,12.17-20.05,0,0,1.77,11.64-12.89,19.61S137.47,432.48,137.47,432.48Z"
                                                style="fill: rgb(245, 245, 245); opacity: 0.5; transform-origin: 160.335px 418.941px;"
                                                class="animable" id="elamh33gsp588"></path>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--magnifying-glass--inject-15" class="animable"
                                    style="transform-origin: 71.55px 203.649px;">
                                    <g id="freepik--Glass--inject-15" class="animable"
                                        style="transform-origin: 71.55px 203.649px;">
                                        <path
                                            d="M61.28,224.53h0a3.18,3.18,0,0,1,1.86-2.61,10,10,0,0,1,9,0,3.18,3.18,0,0,1,1.88,2.6h0l2.68,66.42h0c.1,1.39-.76,2.79-2.59,3.85a13.93,13.93,0,0,1-12.62,0c-1.83-1.05-2.69-2.44-2.61-3.83h0Z"
                                            style="fill: rgb(69, 90, 100); transform-origin: 67.7914px 258.576px;"
                                            id="el8gk6cf60hbj" class="animable"></path>
                                        <path
                                            d="M72.17,221.9c2.5,1.44,2.51,3.77,0,5.22a10,10,0,0,1-9,0c-2.5-1.44-2.51-3.77,0-5.22A10,10,0,0,1,72.17,221.9Z"
                                            style="fill: rgb(55, 71, 79); transform-origin: 67.67px 224.51px;"
                                            id="eloq7kl4dlr7" class="animable"></path>
                                        <path
                                            d="M64.89,206.68a6,6,0,0,1,5.46,0,1.91,1.91,0,0,1,1.13,1.58h0l0,16.26h0a1.94,1.94,0,0,1-1.13,1.58,6,6,0,0,1-5.46,0,1.92,1.92,0,0,1-1.14-1.57l0-16.27h0A1.92,1.92,0,0,1,64.89,206.68Z"
                                            style="fill: #314A48; transform-origin: 67.615px 216.39px;"
                                            id="el74qczygyf6n" class="animable"></path>
                                        <g id="elyvpm6s8yuqc">
                                            <path
                                                d="M64.89,206.68a6,6,0,0,1,5.46,0,1.91,1.91,0,0,1,1.13,1.58h0l0,16.26h0a1.94,1.94,0,0,1-1.13,1.58,6,6,0,0,1-5.46,0,1.92,1.92,0,0,1-1.14-1.57l0-16.27h0A1.92,1.92,0,0,1,64.89,206.68Z"
                                                style="opacity: 0.2; transform-origin: 67.615px 216.39px;"
                                                class="animable" id="elwzgbnva7b9b"></path>
                                        </g>
                                        <g id="elju4px6jse5d">
                                            <path
                                                d="M63.78,217.3l.62.08h.35a11.52,11.52,0,0,0,1.17.09,14.24,14.24,0,0,0,1.46,0,11.24,11.24,0,0,0,1.29-.06,4.91,4.91,0,0,0,.67-.08,4.55,4.55,0,0,0,.73-.09,6.13,6.13,0,0,0,.73-.11,5,5,0,0,0,.53-.11l.17,0,0-8.77h0a1.91,1.91,0,0,0-1.13-1.58,4.82,4.82,0,0,0-1-.39,26.15,26.15,0,0,1-5.63,3.65Z"
                                                style="opacity: 0.2; transform-origin: 67.62px 211.884px;"
                                                class="animable" id="elrq471xf3s5"></path>
                                        </g>
                                        <g id="elnb9b4q58j3e">
                                            <path
                                                d="M69.53,228l1.86,67.81a11.11,11.11,0,0,0,2.74-1.08c1.83-1.06,2.69-2.46,2.59-3.85h0l-2.68-66.41a3.18,3.18,0,0,1-1.86,2.61A8.54,8.54,0,0,1,69.53,228Z"
                                                style="opacity: 0.15; transform-origin: 73.1289px 260.14px;"
                                                class="animable" id="elqf7tjc1lss"></path>
                                        </g>
                                        <path
                                            d="M113,140.35c0-.46,0-.9-.07-1.35s0-.87-.09-1.31c0-.29-.06-.58-.1-.87-.07-.75-.18-1.5-.3-2.21l-.18-1c-.06-.32-.13-.61-.19-.91-.19-.83-.39-1.63-.63-2.4-.12-.37-.23-.73-.35-1.09a.49.49,0,0,1-.06-.17c-.13-.35-.25-.7-.39-1s-.31-.78-.49-1.15a8.59,8.59,0,0,0-.39-.81,4.41,4.41,0,0,0-.28-.58c-.12-.23-.24-.47-.37-.69l-.09-.15c-.11-.2-.24-.4-.36-.6s-.16-.26-.24-.38a19.31,19.31,0,0,0-1.92-2.55l-.35-.4c-.1-.12-.2-.21-.31-.32s-.25-.25-.39-.39a11,11,0,0,0-1.15-1c-.19-.17-.39-.32-.59-.47s-.4-.3-.61-.44a11.19,11.19,0,0,0-1-.65l-.27-.17,0,0h0l-6.22-3.66h0l-.09,0A19.77,19.77,0,0,0,85.55,111c-5.16,0-11,1.69-17.18,5.26C47.16,128.5,30,158.3,30,182.81c0,12.22,4.28,20.82,11.19,24.85l.06,0h0l6.24,3.65s0,0,0,0l.07,0h0l.09.05.1.05a9.84,9.84,0,0,0,.91.48c.38.19.77.36,1.16.51a9.58,9.58,0,0,0,1.06.39,5.18,5.18,0,0,0,.56.17,6.13,6.13,0,0,0,.61.18c.21.06.41.11.62.15a2.63,2.63,0,0,0,.47.1c.26.06.55.11.83.15a.58.58,0,0,0,.19,0l1,.14c5.72.6,12.38-1,19.5-5.13,21.23-12.25,38.44-42.06,38.44-66.56C113.06,141.51,113.05,140.93,113,140.35ZM43,198.18a37.56,37.56,0,0,1-1.3-10.06c0-23.22,17-52.66,37.11-64.27a35.75,35.75,0,0,1,9.41-3.9,20.62,20.62,0,0,1,4.84-.61,14.79,14.79,0,0,1,2.46.19l.06,0c4.12,4.86,4.73,12.67,4.73,16.84,0,23.22-17,52.66-37.1,64.26-5.12,3-10,4.52-14.26,4.52a9.44,9.44,0,0,1-2.59-.34h0A18.3,18.3,0,0,1,43,198.18Z"
                                            style="fill: rgb(55, 71, 79); transform-origin: 71.55px 162.401px;"
                                            id="el7whtv8y1oh4" class="animable"></path>
                                        <g id="elxvbezuikrv">
                                            <path
                                                d="M107.53,140.45c0,12.34-4.79,26.42-12.3,38.67-.36.58-.72,1.15-1.09,1.72s-.63,1-1,1.47c-.71,1.05-1.42,2.08-2.17,3.09q-.53.73-1.08,1.44c-.39.51-.79,1-1.2,1.54-.77,1-1.57,1.92-2.38,2.85-.53.62-1.08,1.22-1.63,1.81a62.41,62.41,0,0,1-14.29,11.67c-1,.56-1.91,1.06-2.85,1.52a28.47,28.47,0,0,1-9.78,2.92c-.55,0-1.1.08-1.62.08q-.42,0-.81,0h0a12.39,12.39,0,0,1-1.53-.15l-.73-.13a5.53,5.53,0,0,1-.7-.18,11.15,11.15,0,0,1-6-3.9,9.51,9.51,0,0,0,2.59.33c4.22,0,9.14-1.55,14.26-4.51,20.12-11.6,37.09-41,37.09-64.26,0-4.17-.6-12-4.72-16.85l-.07,0c5.25.84,8.23,4.41,9.91,8.53a23.53,23.53,0,0,1,1.15,3.6,1.94,1.94,0,0,0,.05.2c0,.22.1.45.14.67s.09.43.13.65l0,.16c0,.24.09.47.12.7.14.86.24,1.7.32,2.5,0,.24,0,.46,0,.68s0,.34,0,.5v.21c0,.18,0,.35,0,.53v.15c0,.22,0,.44,0,.65C107.53,139.68,107.53,140.08,107.53,140.45Z"
                                                style="opacity: 0.25; transform-origin: 76.95px 164.405px;"
                                                class="animable" id="elvlurk2zgepg"></path>
                                        </g>
                                        <path
                                            d="M46.9,211l-5.76-3.39C34.22,203.61,30,195,30,182.82c0-10.93,3.43-22.91,9.09-33.91l6.58,3.78c-5.68,11-9.44,22.85-9.44,33.78C36.18,198.42,40.25,206.9,46.9,211Z"
                                            style="fill: rgb(38, 50, 56); transform-origin: 38.45px 179.955px;"
                                            id="elhsfkhb5tm9l" class="animable"></path>
                                        <path
                                            d="M101.86,117.26l0-.05h0l-6.22-3.66h0l-.09,0A19.75,19.75,0,0,0,85.55,111c-5.16,0-11,1.69-17.18,5.25-11.76,6.8-22.28,19-29.33,32.67l6.54,3.85c0,.09-.09.18-.14.27,7.07-13.54,17.54-25.79,29.18-33.12,10.53-6.64,20.52-6.73,27.5-2.49Z"
                                            style="fill: rgb(69, 90, 100); transform-origin: 70.58px 132.019px;"
                                            id="elbjngqpslncd" class="animable"></path>
                                        <g id="elinprzx6swuq">
                                            <path
                                                d="M104.19,138.48c0,23.22-17,52.66-37.11,64.27a35.75,35.75,0,0,1-9.41,3.9,20.62,20.62,0,0,1-4.84.61,13.73,13.73,0,0,1-4.92-.84A16.57,16.57,0,0,1,43,198.18a37.56,37.56,0,0,1-1.3-10.06c0-23.22,17-52.66,37.11-64.27a35.75,35.75,0,0,1,9.41-3.9,20.62,20.62,0,0,1,4.84-.61,13.73,13.73,0,0,1,4.92.84,16.53,16.53,0,0,1,4.91,8.24A37.56,37.56,0,0,1,104.19,138.48Z"
                                                style="fill: #314A48; opacity: 0.1; transform-origin: 72.945px 163.3px;"
                                                class="animable" id="eloyjrtg60yuc"></path>
                                        </g>
                                        <g id="elcpkir425vfk">
                                            <path
                                                d="M52.83,207.26a20.62,20.62,0,0,0,4.84-.61,35.75,35.75,0,0,0,9.41-3.9c20.12-11.61,37.11-41.05,37.11-64.27a37.56,37.56,0,0,0-1.3-10.06A16.53,16.53,0,0,0,98,120.19l-.24-.08-.67-.22-.4-.11c-.23-.06-.46-.11-.7-.15l-.41-.09h0c4.12,4.87,4.72,12.68,4.72,16.85,0,23.22-17,52.66-37.09,64.26-5.12,3-10,4.51-14.26,4.51a9.51,9.51,0,0,1-2.59-.33l0,0a11.49,11.49,0,0,0,1.51,1.53A13.73,13.73,0,0,0,52.83,207.26Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.3; transform-origin: 75.2755px 163.4px;"
                                                class="animable" id="el7u6i5fy64td"></path>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--Character--inject-15" class="animable animator-hidden"
                                    style="transform-origin: 238.411px 187.752px;">
                                    <g id="freepik--character--inject-15" class="animable"
                                        style="transform-origin: 238.411px 187.752px;">
                                        <g id="freepik--character--inject-15" class="animable"
                                            style="transform-origin: 238.411px 187.752px;">
                                            <g id="freepik--character--inject-15" class="animable"
                                                style="transform-origin: 238.411px 187.752px;">
                                                <path
                                                    d="M241.46,221.67a4.86,4.86,0,0,1,3.28.58,15,15,0,0,1,2.55,5.33c.74,2.67,1.08,3.73,2.79,5a18.89,18.89,0,0,1,5.48,6,18.39,18.39,0,0,1,2.3,11.8c-.63,4.74-2.57,6.61-4.35,7.49a8.46,8.46,0,0,1-3.34.6Z"
                                                    style="fill: rgb(38, 50, 56); transform-origin: 249.751px 240.034px;"
                                                    id="ele4zfcb7kcm4" class="animable"></path>
                                                <path
                                                    d="M232.72,221.19l.95.87a14,14,0,0,1,1.58,1.39c2,2.18,1.65,7,1.52,8.32a3,3,0,0,0-2.36.66c-1.55,1.1-4.08,3.25-3.87,4.12s4.47,2.74,8.5,7.72a39.42,39.42,0,0,1,5.56,9.25c1.12,2.29,3.31,6,6.87,4.69,1.85-.64,5.8-4.91,4.75-12.88-1-7.46-4.88-10.21-7.52-12.14s-2.93-4-4.39-7.61c-.64-1.59-1.07-2.85-2.85-3.91-2.35-.68-4.69-.06-6.42-.24C233.63,221.28,233.12,220.45,232.72,221.19Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 243.461px 239.685px;"
                                                    id="elsh2pz18tvd8" class="animable"></path>
                                                <path
                                                    d="M237.32,169.84a4.8,4.8,0,0,1-2.9,2.67c-8,2.65-18.33,6-28.1,10.17-12.12,5.19-15.7,8.25-18.64,13.51-1.79,3.22-1.56,7.66-.58,10.71s3.45,7.31,9.68,10.8c12.38,6.94,19.56,11.21,27.07,14.77,0,0,6.68-7.14,7.39-13.31l-10.91-9.95,25.76-12.14,33-29.93Z"
                                                    style="fill: rgb(69, 90, 100); transform-origin: 232.719px 199.805px;"
                                                    id="elbbyjxb1w6xc" class="animable"></path>
                                                <path
                                                    d="M219.88,207.48a15.88,15.88,0,0,0-6.56,9.94c-.44,2.07-1.07,5.67-1.57,8.71,4.44,2.44,8.25,4.51,12.1,6.34,0,0,6.68-7.14,7.39-13.31l-10.91-9.95,1.5-.7Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 221.495px 219.975px;"
                                                    id="el5g8kzslf1e8" class="animable"></path>
                                                <path
                                                    d="M246.09,197.07,239,175.16l-3.6,5.45a28.5,28.5,0,0,0,2.74,10.57s-12,9.21-13.42,10.09c-5.32,3.31-6,2.85-9,.7a12.11,12.11,0,0,0-11.05-1.72c7.44-.53,9.27,2.55,15.73,9l25.76-12.14Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 225.415px 192.205px;"
                                                    id="elnq7osvx7djm" class="animable"></path>
                                                <path
                                                    d="M212.09,281c0,2.39.88,5.94-.48,8.68a8,8,0,0,1-4.57,4A10.31,10.31,0,0,0,204.1,295c-1,.84-2,1.69-3,2.52a35.6,35.6,0,0,0-4.28,3.44c-3.49,3.73-9,4.89-14,4.53a18.07,18.07,0,0,1-7.09-1.79c-1.59-.78-3.51-2.29-3.5-4.23s1.53-2.72,3-3.78a41,41,0,0,1,6-3.35,31.06,31.06,0,0,0,6.07-3.8c1.94-1.7,7.38-5,10.69-12.13,1.51-3.26,6.51-17.84,6.51-17.84l17.69,5.3S212.1,278.6,212.09,281Z"
                                                    style="fill: rgb(255, 189, 167); transform-origin: 197.21px 282.061px;"
                                                    id="eljzx2xcb7t" class="animable"></path>
                                                <path
                                                    d="M212.16,290.41a5.37,5.37,0,0,1-.69,3.58,16.46,16.46,0,0,1-5.88,2.68c-2.93.76-4.1,1.12-5.49,3a16.67,16.67,0,0,1-5.94,5.61A19.6,19.6,0,0,1,179,307.07c-4.67-1.19-6.24-3.72-6.59-4.42a5.31,5.31,0,0,1-.37-3.1Z"
                                                    style="fill: rgb(38, 50, 56); transform-origin: 192.09px 299.128px;"
                                                    id="el5fq33gut6n4" class="animable"></path>
                                                <path
                                                    d="M212.57,279.48a17.94,17.94,0,0,0-.68,1.86,4.19,4.19,0,0,1-1.63,2.07c-2.42,1.64-5.86,1.6-7.28,1.43a2.94,2.94,0,0,0-1.63-3.1c-2.81-1.41-5.48-2.32-6.39-1.94-2.1.88-.48,1.72-7,7.13a59,59,0,0,1-11,7.36c-2.44,1.36-5.79,3.62-4.74,6.36.67,2,5.23,6.31,13.66,5.57,7.78-.68,11.39-5.26,13.55-8.1s4.39-3.18,8.39-4.72c1.75-.67,3.13-1.06,4.33-3,.78-2.55.35-6,.58-7.92C212.93,281,213.36,279.94,212.57,279.48Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 192.525px 292.892px;"
                                                    id="elv9wro1mebul" class="animable"></path>
                                                <path
                                                    d="M234.36,174.52l5-5L287,175.8c.08,4.75-.88,14.86-2.78,19.94-1.74,4.65-4,9.62-15,15.52S245,223.12,242.32,224.62c-3.85,2.18-3.75,2.27-4.66,9.08-.93,7-4.27,14.85-8.93,21.85-3.65,5.48-14,22.8-14,22.8-6.81,1.35-12.36-2.57-15.42-6.32,0,0,13.9-44,15.31-51.26.79-4.05,1.57-7.69,6.42-12.27,4.16-3.92,20.29-17.67,20.29-17.67S234.91,185.19,234.36,174.52Z"
                                                    style="fill: rgb(69, 90, 100); transform-origin: 243.157px 224.071px;"
                                                    id="eljgnx9r1bqrn" class="animable"></path>
                                                <path
                                                    d="M278.88,179.87c.52,10.1.83,16.2-12.95,23.24-4.15,2.12-8.47,4.13-12.64,6.08-12.38,5.79-20.81,12.07-21.47,16.67-.25,1.75-.39,3.84-.56,6.27-.4,5.72-.73,10.46-5.44,19.52-3.29,6.3-10.14,19.29-13.57,24.56l1.26.13c3.34-5.35,9.66-17,13.28-24.06,4.83-9.4,5.2-14.3,5.6-20.08.17-2.39.32-4.47.56-6.18.57-4,9.72-10.61,20.82-15.8,4.18-1.95,8.51-4,12.68-6.1,14.44-7.37,14.09-14.11,13.56-24.3,0-1-.09-2-.13-3l-1.15-.22C278.77,177.72,278.82,178.82,278.88,179.87Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 246.222px 226.47px;"
                                                    id="elb797gzpsyt" class="animable"></path>
                                                <path
                                                    d="M270.29,201.73c-.17-.13-4.24-3.18-2.35-10.53A42.16,42.16,0,0,0,269.27,174l1.13-.11A43.42,43.42,0,0,1,269,191.48c-1.69,6.61,1.88,9.3,1.92,9.32Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 269.18px 187.81px;"
                                                    id="el0di2ox1juaa4" class="animable"></path>
                                                <path
                                                    d="M200.88,267c1.67,2.82,11.79,8.33,16.78,6.65l-2.91,5c-3.7,1.7-12-1.62-16-5.84Z"
                                                    style="fill: rgb(55, 71, 79); transform-origin: 208.205px 273.052px;"
                                                    id="elnmff2u1ycl" class="animable"></path>
                                                <g id="freepik--Top--inject-15" class="animable"
                                                    style="transform-origin: 245.758px 138.684px;">
                                                    <g id="freepik--Arm--inject-15" class="animable"
                                                        style="transform-origin: 241.32px 139.42px;">
                                                        <path id="freepik--arm--inject-15"
                                                            d="M250.16,112.36c-8.36,0-13.28,3.63-13.28,10.63,0,6.07,2.27,25,2.27,25l-12.5,9.84,7.22,8.65s12.34-6.26,16.61-8.59c6.51-3.56,5.69-4.92,5.16-13.16S250.16,112.36,250.16,112.36Z"
                                                            style="fill: rgb(235, 235, 235); transform-origin: 241.32px 139.42px;"
                                                            class="animable"></path>
                                                        <path
                                                            d="M229,156l4.28-3.36a12.8,12.8,0,0,1,4.86,5.45,13.27,13.27,0,0,1,1.23,5.61l-4.55,2.32S234.11,158.78,229,156Z"
                                                            style="fill: rgb(224, 224, 224); transform-origin: 234.185px 159.33px;"
                                                            id="eln1xabv8ncum" class="animable"></path>
                                                    </g>
                                                    <path id="freepik--Chest--inject-15"
                                                        d="M262.45,112.34c7.06.48,10.16,1.87,18.64,3.65,2,1.85,5,9.68,5.94,19.5,1.17,12.8,1.24,17.74.9,33.38a117.34,117.34,0,0,1-1.27,16.36c-12.56,7.59-44.42,3.48-53.4-12.44,2.23-3.38,3.2-14,3.68-37.37.29-13.73,8-22.89,13.34-23.07C252.93,112.29,257.92,112,262.45,112.34Z"
                                                        style="fill: rgb(245, 245, 245); transform-origin: 260.684px 150.548px;"
                                                        class="animable"></path>
                                                    <g id="freepik--Head--inject-15" class="animable"
                                                        style="transform-origin: 258.169px 96.5933px;">
                                                        <path
                                                            d="M242.29,87.26a7.74,7.74,0,0,1-3.06-3.66,12.16,12.16,0,0,1-.87-5.3,6,6,0,0,1,.44-1.84,2.66,2.66,0,0,1,2-1c.49-.09,1-.16,1.47-.23A9.6,9.6,0,0,1,240,72.09a5,5,0,0,1-.24-2.87,1.46,1.46,0,0,1,.3-.71,1.66,1.66,0,0,1,1.66-.26,32.26,32.26,0,0,0,10.54.54c2.79-.23,4.18-.6,8-1.06s10.72,1.12,12.09,8.32c5.16.72,6,9,4.66,12.53-2.54,6.51-5,10.83-6.26,15.51-.27-.18-17.75-9.7-21.77-12.39l-4.52-3C243.69,88.18,243,87.74,242.29,87.26Z"
                                                            style="fill: rgb(38, 50, 56); transform-origin: 257.957px 85.8733px;"
                                                            id="el3985yvjoenv" class="animable"></path>
                                                        <path
                                                            d="M275.58,99.19c-3,2.38-5,.24-5,.24l.5,14.71c-1.54,2.12-8.08,3.56-11.06,5.51-1.15.75-3,3.74-4.93,5.88-1.12-2.44-3.54-8.29-.18-12.34l-.08-2.81a25.26,25.26,0,0,1-4.7-.07,7.77,7.77,0,0,1-2.76-.93,8.41,8.41,0,0,1-3.88-4.9,41,41,0,0,1-1.77-11.15c-.15-4.54.13-10.42,1.54-13.5,2.64-2.48,10.72-3,17.57-1.22.18,1.69,0,3.18,1.46,5a7.26,7.26,0,0,0,2.66,2c.29,3.29.45,4.18,1.1,5.62.37.82,1.08,2.06,2.74,1.4.78-.31,1.31-2.08,2.1-3.27,1-1.6,4.49-2.44,6.27.9A7.45,7.45,0,0,1,275.58,99.19Z"
                                                            style="fill: rgb(255, 189, 167); transform-origin: 259.842px 101.543px;"
                                                            id="elxqt6legu8fp" class="animable"></path>
                                                        <path
                                                            d="M254.79,110.38c3.42-.45,9.5-1.94,11.2-4.09s2.93-7,2.93-7-.51,5.46-1.78,7.86-4.21,3.45-6.35,4.17a37.53,37.53,0,0,1-5.94,1.17Z"
                                                            style="fill: rgb(240, 153, 122); transform-origin: 261.855px 105.89px;"
                                                            id="el4hh22mrsrsl" class="animable"></path>
                                                        <path
                                                            d="M255.65,87.58l3.49-.78a1.88,1.88,0,0,0-2.2-1.39A1.75,1.75,0,0,0,255.65,87.58Z"
                                                            style="fill: rgb(38, 50, 56); transform-origin: 257.363px 86.4761px;"
                                                            id="el9exo69a35ci" class="animable"></path>
                                                        <path
                                                            d="M247.19,86.11l-2.84,2.18a1.87,1.87,0,0,1,.35-2.57A1.74,1.74,0,0,1,247.19,86.11Z"
                                                            style="fill: rgb(38, 50, 56); transform-origin: 245.59px 86.8252px;"
                                                            id="el7ieic5bjq83" class="animable"></path>
                                                        <path
                                                            d="M256.54,100.2l-3.69,1.55a2.1,2.1,0,0,0,2.69,1.1A2,2,0,0,0,256.54,100.2Z"
                                                            style="fill: rgb(177, 102, 104); transform-origin: 254.784px 101.598px;"
                                                            id="elzmwfymbv2i" class="animable"></path>
                                                        <path
                                                            d="M256.45,101a2,2,0,0,0-1.89,2,2.07,2.07,0,0,0,1-.13,1.94,1.94,0,0,0,1.19-1.84Z"
                                                            style="fill: rgb(255, 168, 167); transform-origin: 255.655px 102.007px;"
                                                            id="elln25u6aepx" class="animable"></path>
                                                        <polygon
                                                            points="253.05 88.5 252.3 99.26 246.68 97.1 253.05 88.5"
                                                            style="fill: rgb(240, 153, 122); transform-origin: 249.865px 93.88px;"
                                                            id="elust9bz89yt" class="animable"></polygon>
                                                        <path
                                                            d="M258.83,90.67a1.47,1.47,0,1,1-1.53-1.39A1.47,1.47,0,0,1,258.83,90.67Z"
                                                            style="fill: rgb(38, 50, 56); transform-origin: 257.362px 90.7487px;"
                                                            id="elp5rgd6zgnq" class="animable"></path>
                                                        <path
                                                            d="M247.85,91.06a1.46,1.46,0,0,1-1.47,1.46A1.48,1.48,0,0,1,244.92,91a1.46,1.46,0,0,1,1.48-1.45A1.45,1.45,0,0,1,247.85,91.06Z"
                                                            style="fill: rgb(38, 50, 56); transform-origin: 246.385px 91.035px;"
                                                            id="el5c0t56rfdzm" class="animable"></path>
                                                    </g>
                                                    <g id="freepik--Laptop--inject-15" class="animable"
                                                        style="transform-origin: 227.104px 179.9px;">
                                                        <path
                                                            d="M267.56,191.89l0,.89a2.54,2.54,0,0,1-1.22,2.21l-23.75,14.36a2.55,2.55,0,0,1-1.25.36,2.49,2.49,0,0,1-1.27-.31l-44.66-24.52a2.54,2.54,0,0,1-1.31-2.16v-.61L241.32,208Z"
                                                            style="fill: rgb(235, 235, 235); transform-origin: 230.83px 195.91px;"
                                                            id="ellcbuhlrrhor" class="animable"></path>
                                                        <path
                                                            d="M194.11,182.11l25.83-15.66a1.53,1.53,0,0,1,1.51,0l45.93,25.23a.33.33,0,0,1,0,.58L241.32,208Z"
                                                            style="fill: rgb(250, 250, 250); transform-origin: 230.831px 187.125px;"
                                                            id="elydfm1opgbg" class="animable"></path>
                                                        <polygon
                                                            points="241.51 205.81 255.45 197.37 213.69 174.44 199.68 182.83 241.51 205.81"
                                                            style="fill: rgb(224, 224, 224); transform-origin: 227.565px 190.125px;"
                                                            id="elckphkyd892" class="animable"></polygon>
                                                        <polygon
                                                            points="247.96 183.03 247.71 183.18 240.88 187.32 228.43 180.48 228.17 180.34 235.26 176.05 247.96 183.03"
                                                            style="fill: rgb(224, 224, 224); transform-origin: 238.065px 181.685px;"
                                                            id="elnot8dtfiud" class="animable"></polygon>
                                                        <polygon
                                                            points="247.71 183.18 240.88 187.32 228.43 180.48 235.26 176.34 247.71 183.18"
                                                            style="fill: rgb(230, 230, 230); transform-origin: 238.07px 181.83px;"
                                                            id="elnehhyck6y1a" class="animable"></polygon>
                                                        <path
                                                            d="M241.32,208l0,1.68a2.49,2.49,0,0,1-1.27-.31l-44.66-24.52a2.54,2.54,0,0,1-1.31-2.16v-.61Z"
                                                            style="fill: rgb(235, 235, 235); transform-origin: 217.7px 195.88px;"
                                                            id="elm0zgn5vq7u" class="animable"></path>
                                                        <g id="el9ydx5siwsi8">
                                                            <g style="opacity: 0.5; mix-blend-mode: multiply; transform-origin: 217.7px 195.88px;"
                                                                class="animable" id="elt0vrl9z0wb">
                                                                <path
                                                                    d="M241.32,208l0,1.68a2.49,2.49,0,0,1-1.27-.31l-44.66-24.52a2.54,2.54,0,0,1-1.31-2.16v-.61Z"
                                                                    style="fill: rgb(224, 224, 224); transform-origin: 217.7px 195.88px;"
                                                                    id="el5u3ianki5wj" class="animable"></path>
                                                            </g>
                                                        </g>
                                                        <path
                                                            d="M229.37,155.74a27.41,27.41,0,0,1-7.89,4.64c-5.41,1.94-6.53,1.9-10.77,5.15a37,37,0,0,0-4.11,3.9c-1.23,2.18-3,4-4.35,6.13-1.12,1.24.27,1.6,1.36,1.42,3-.64,4.79-3.52,7.21-5.11a10.4,10.4,0,0,0,.22,3c.18.81.23,1.82,1.06,2.24a6.27,6.27,0,0,0,1.83.31,1.26,1.26,0,0,0,.51.7,2.12,2.12,0,0,0,1.51,0,6.49,6.49,0,0,0,.78-.31,1.52,1.52,0,0,0,1.17.95,6.73,6.73,0,0,0,3.44-.66c.15-.11,1.26-1.09,1.59-1.37s.92-.67,1.07-1.07-.81-1.27-1.49-1.22a6.53,6.53,0,0,0-2.14.64,7.92,7.92,0,0,0,.21-2c.37-1.08,4.88-.52,8.52-2,2.75-1.16,3.48-3.93,4.77-4.59l1.15-.58C235,160.93,232.64,157.35,229.37,155.74Z"
                                                            style="fill: rgb(255, 189, 167); transform-origin: 218.428px 167.259px;"
                                                            id="el9mpy8u5xyn" class="animable"></path>
                                                        <path
                                                            d="M240.62,208.42a3,3,0,0,1-2.58-.14l-44.46-24.42a3,3,0,0,1-1.5-2.06l-5.42-28.57a2.63,2.63,0,0,1,0-.49,2.94,2.94,0,0,1,.84-2.14L233.65,176a2.22,2.22,0,0,1,1.11,1.52Z"
                                                            style="fill: #314A48; transform-origin: 213.634px 179.623px;"
                                                            id="elr9yj3b9ao4" class="animable"></path>
                                                        <g id="elopzykib8rsc">
                                                            <g style="opacity: 0.7; transform-origin: 213.634px 179.623px;"
                                                                class="animable" id="eluldz5ltgoee">
                                                                <path
                                                                    d="M240.62,208.42a3,3,0,0,1-2.58-.14l-44.46-24.42a3,3,0,0,1-1.5-2.06l-5.42-28.57a2.63,2.63,0,0,1,0-.49,2.94,2.94,0,0,1,.84-2.14L233.65,176a2.22,2.22,0,0,1,1.11,1.52Z"
                                                                    style="fill: rgb(255, 255, 255); transform-origin: 213.634px 179.623px;"
                                                                    id="elvxrzdeme2jq" class="animable"></path>
                                                            </g>
                                                        </g>
                                                        <path
                                                            d="M241.32,208l-.3.18a3,3,0,0,1-.4.21l-5.86-30.92a2.22,2.22,0,0,0-1.11-1.52l-46.2-25.38a2.88,2.88,0,0,1,.6-.48l46.29,25.43a2.19,2.19,0,0,1,1.11,1.52Z"
                                                            style="fill: rgb(250, 250, 250); transform-origin: 214.385px 179.24px;"
                                                            id="elhg6tvplhehu" class="animable"></path>
                                                        <path
                                                            d="M215.32,182.48c-.37-1.91-2.4-4.42-4.55-5.6s-3.6-.59-3.24,1.33,2.4,4.42,4.55,5.6S215.68,184.39,215.32,182.48Z"
                                                            style="fill: #314A48; transform-origin: 211.425px 180.344px;"
                                                            id="el550me3dic69" class="animable"></path>
                                                    </g>
                                                    <g id="freepik--arm--inject-15" class="animable"
                                                        style="transform-origin: 271.47px 153.47px;">
                                                        <path
                                                            d="M303.1,156.38c-2.61-11.08-7.24-23.68-10.2-31.94-1.58-4.38-4-6.21-10.92-8-4.84,7.13-3.53,18.28-1.78,22.4l6.65,18.7s-6.05,5.57-11.75,9.81c-4.82,3.57-7.3,5.64-13,5.6-6.64,0-7.57.21-10.54,1.53-1.58.69-5.66,3-7.25,3.87-1.21.65-3.43,3-5.47,4.5s.47,2.21,2,1.76a22.55,22.55,0,0,0,4.89-2.29,26.48,26.48,0,0,1,3.84-1.49,23.07,23.07,0,0,0-.79,4.5c0,.82-.17,1.81.54,2.41a6.35,6.35,0,0,0,1.73.7,1.2,1.2,0,0,0,.34.79,2,2,0,0,0,1.46.37,6.15,6.15,0,0,0,.84-.13,1.5,1.5,0,0,0,.93,1.18,6.6,6.6,0,0,0,3.5.1c.17-.07,1.47-.78,1.85-1a3.41,3.41,0,0,0,1.28-.82c.29-.42-.52-1.41-1.19-1.51a6.7,6.7,0,0,0-2.23.16,7.68,7.68,0,0,0,.64-1.93c.61-1,5,0,8.88-.7,2.94-.53,5.05-3.33,6.37-3.93,8.44-3.86,14.89-6.57,23.4-11.77C304.85,164.5,304.51,162.34,303.1,156.38Z"
                                                            style="fill: rgb(255, 189, 167); transform-origin: 270.985px 153.69px;"
                                                            id="elcmaeg4hic0g" class="animable"></path>
                                                        <path
                                                            d="M248.77,181.07c.44-1.12,1.13-2.44,2-2.75a10.26,10.26,0,0,0-1.22,2.48Z"
                                                            style="fill: rgb(240, 153, 122); transform-origin: 249.77px 179.695px;"
                                                            id="elzhppddzo4or" class="animable"></path>
                                                        <path
                                                            d="M251,188.41a8.9,8.9,0,0,1-.09-2.42,21.26,21.26,0,0,1,.3-2.4,10.24,10.24,0,0,1,.64-2.35,4.4,4.4,0,0,1,1.44-2,3.59,3.59,0,0,0-.66,1,7.36,7.36,0,0,0-.45,1.09,19.37,19.37,0,0,0-.52,2.31c-.14.79-.27,1.57-.4,2.36A19,19,0,0,0,251,188.41Z"
                                                            style="fill: rgb(240, 153, 122); transform-origin: 252.078px 183.825px;"
                                                            id="eli0825n8clwa" class="animable"></path>
                                                        <path
                                                            d="M253.64,189.44a11.14,11.14,0,0,1,0-2.38,18.76,18.76,0,0,1,.35-2.35,15.35,15.35,0,0,1,.63-2.3,4.52,4.52,0,0,1,1.21-2,2.89,2.89,0,0,0-.53,1c-.13.37-.24.74-.34,1.12-.19.75-.37,1.52-.52,2.28C254.11,186.34,253.81,187.87,253.64,189.44Z"
                                                            style="fill: rgb(240, 153, 122); transform-origin: 254.703px 184.925px;"
                                                            id="elw8i6v2vmu" class="animable"></path>
                                                        <path
                                                            d="M260,187.41a4.13,4.13,0,0,0-1,.14c-.32.08-.54.24-.94.37l.16-.26a1,1,0,0,1-.06.47,1.21,1.21,0,0,1-.22.37,1.34,1.34,0,0,1-.67.44,3.54,3.54,0,0,0,.4-.61,1.21,1.21,0,0,0,.09-.31.92.92,0,0,0,0-.27l0-.18.19-.08a2.56,2.56,0,0,1,.92-.2A2,2,0,0,1,260,187.41Z"
                                                            style="fill: rgb(240, 153, 122); transform-origin: 258.635px 188.098px;"
                                                            id="el3tnmwxt0nxv" class="animable"></path>
                                                        <path
                                                            d="M281.09,116c2.61.42,7.8.89,10.57,4.94s4.28,9.23,6.51,16.07,5.68,18.43,6.43,22.09,0,5.71-3.3,8-13,7.9-17.8,9.45a14.64,14.64,0,0,0-3.3-7.15,15.1,15.1,0,0,0-3.67-3.21l9.9-8.72s-6.38-18.27-7.48-21S276.42,123.06,281.09,116Z"
                                                            style="fill: rgb(235, 235, 235); transform-origin: 290.698px 146.275px;"
                                                            id="elxe5p8sjcmhe" class="animable"></path>
                                                        <path
                                                            d="M277.84,165.08l-4.65,3.65a15.65,15.65,0,0,1,4.44,4.49,11.83,11.83,0,0,1,2,5.1l6-2.52S284.82,166.72,277.84,165.08Z"
                                                            style="fill: rgb(224, 224, 224); transform-origin: 279.41px 171.7px;"
                                                            id="elqd9atgyisz" class="animable"></path>
                                                    </g>
                                                    <g id="freepik--Tie--inject-15" class="animable"
                                                        style="transform-origin: 261.452px 136.926px;">
                                                        <path
                                                            d="M255.05,125.53a43.33,43.33,0,0,0,3.68-6.5c1-2,4.38-2.43,7.86-4.61a12.26,12.26,0,0,0,4.31-4.31l-.08-2.26c1.22,0,1.24,1,1.78,2.25s2.37,4.15,3.81,4.55c-1.54,4.33-6.81,9.43-12.51,11.79-2-4.53-4.12-5.86-4.79-5C257.49,123.39,256.64,124.61,255.05,125.53Z"
                                                            style="fill: rgb(230, 230, 230); transform-origin: 265.73px 117.145px;"
                                                            id="ela70ihyrneiq" class="animable"></path>
                                                        <path
                                                            d="M255.05,125.53c-.77-1.84-.59-4.85-1.08-7.72-.18-1.1.9-4.62.9-4.62l-.08-2.81-1.68.08a7.36,7.36,0,0,1-1.51,1.85c-2.12,1.87-3.74,8.18-3,10.72.68-2.34,2.48-4,3.53-3.56S253.64,124.11,255.05,125.53Z"
                                                            style="fill: rgb(230, 230, 230); transform-origin: 251.734px 117.955px;"
                                                            id="el33n0rl1jlh5" class="animable"></path>
                                                        <path
                                                            d="M256.26,130c0,5.75.66,20.78,1.17,25,.4,3.35-4.37,10.8-5.8,11-.87.13-4.94-6.56-5.13-11.32-.17-4.33,3.48-18.89,6.2-25.49A1.87,1.87,0,0,1,256.26,130Z"
                                                            style="fill: #314A48; transform-origin: 251.974px 147.061px;"
                                                            id="el5fupktmrd4t" class="animable"></path>
                                                        <path
                                                            d="M253.06,123.8c-1-1.29-.68-4.15-.68-4.15-.55-.65-1.88,0-2.75,1.27,0,0,.38,4,2.75,5.85Z"
                                                            style="fill: #314A48; transform-origin: 251.345px 123.082px;"
                                                            id="elj2gaclsz1g" class="animable"></path>
                                                        <g id="els3y62pcjykp">
                                                            <path
                                                                d="M252.38,126.77l.68-3a2.91,2.91,0,0,1-.2-.35h0A2.88,2.88,0,0,0,251.6,126,5.17,5.17,0,0,0,252.38,126.77Z"
                                                                style="opacity: 0.15; transform-origin: 252.327px 125.095px;"
                                                                class="animable" id="elk7fmmoob64p"></path>
                                                        </g>
                                                        <g id="elnz9hn23g9il">
                                                            <path
                                                                d="M252.38,119.65c-.55-.65-1.88,0-2.75,1.27a10.77,10.77,0,0,0,.29,1.55c.41-1.13,1.38-2.26,2-2.11a1.33,1.33,0,0,1,.46.23C252.34,120,252.38,119.65,252.38,119.65Z"
                                                                style="opacity: 0.15; transform-origin: 251.005px 120.932px;"
                                                                class="animable" id="eljot498fk48"></path>
                                                        </g>
                                                        <path
                                                            d="M257.18,124.81c1.54-.76,3.23-3.35,3.23-3.35a9.55,9.55,0,0,1,2.58,3.17,13.17,13.17,0,0,1-5.19,2.44Z"
                                                            style="fill: #314A48; transform-origin: 260.085px 124.265px;"
                                                            id="elxuil29ry8ho" class="animable"></path>
                                                        <g id="el5zg8maahsjs">
                                                            <path
                                                                d="M260.41,121.46,260,122a9.35,9.35,0,0,1,2.25,3.16,7.7,7.7,0,0,0,.7-.52A9.55,9.55,0,0,0,260.41,121.46Z"
                                                                style="opacity: 0.15; transform-origin: 261.475px 123.31px;"
                                                                class="animable" id="el2i45je40ou3"></path>
                                                        </g>
                                                        <path
                                                            d="M255.13,123.64c-2.33-.45-2.59.74-3,1.89-.28.72-.08,3.05.6,3.71a3.76,3.76,0,0,1,3.56.75c.9-.9,1.81-2.5,1.66-3.48A3.36,3.36,0,0,0,255.13,123.64Z"
                                                            style="fill: #314A48; transform-origin: 254.987px 126.766px;"
                                                            id="el0a2bvt4k3s3c" class="animable"></path>
                                                        <g id="elqr393cqgwag">
                                                            <path
                                                                d="M256.26,130c.9-.9,1.81-2.5,1.66-3.48a3.42,3.42,0,0,0-1.42-2.31,3.48,3.48,0,0,1,.25,3.59c-.65.83-.95.69-1.89.58s-2.16.49-2.16.87A3.76,3.76,0,0,1,256.26,130Z"
                                                                style="opacity: 0.15; transform-origin: 255.318px 127.105px;"
                                                                class="animable" id="el6ei22gpqv54"></path>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--curve-chart--inject-15" class="animable"
                                    style="transform-origin: 178.532px 91.9773px;">
                                    <g id="freepik--speech-bubble--inject-15" class="animable"
                                        style="transform-origin: 178.532px 91.9773px;">
                                        <path
                                            d="M222,130.35c-13.93,0-21.29-7.08-21.29-20.49h.72c0,12.93,7.11,19.77,20.57,19.77Z"
                                            style="fill: #314A48; transform-origin: 211.355px 120.105px;"
                                            id="elc20tt1s1lpr" class="animable"></path>
                                        <g id="elscr4hociqwc">
                                            <path
                                                d="M222,130.35c-13.93,0-21.29-7.08-21.29-20.49h.72c0,12.93,7.11,19.77,20.57,19.77Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.8; transform-origin: 211.355px 120.105px;"
                                                class="animable" id="elskbyc2qv42"></path>
                                        </g>
                                        <path
                                            d="M134,148.42a1.07,1.07,0,0,1-.48-1V82.15A2.73,2.73,0,0,1,134.79,80l77-44.43a1.06,1.06,0,0,1,1.08-.07,1.07,1.07,0,0,1,.48,1v65.29a2.73,2.73,0,0,1-1.25,2.16l-77,44.43a1.38,1.38,0,0,1-.65.19A.92.92,0,0,1,134,148.42Zm78-112.31-77,44.43a2.07,2.07,0,0,0-.93,1.61v65.29a.36.36,0,0,0,.62.35l77-44.43a2.1,2.1,0,0,0,.93-1.61V36.46a.5.5,0,0,0-.16-.43.28.28,0,0,0-.13,0A.71.71,0,0,0,212.06,36.11Z"
                                            style="fill: #314A48; transform-origin: 173.435px 91.9773px;"
                                            id="eljm8k59iusl8" class="animable"></path>
                                        <g id="eltivl5j2xmrh">
                                            <path
                                                d="M134,148.42a1.07,1.07,0,0,1-.48-1V82.15A2.73,2.73,0,0,1,134.79,80l77-44.43a1.06,1.06,0,0,1,1.08-.07,1.07,1.07,0,0,1,.48,1v65.29a2.73,2.73,0,0,1-1.25,2.16l-77,44.43a1.38,1.38,0,0,1-.65.19A.92.92,0,0,1,134,148.42Zm78-112.31-77,44.43a2.07,2.07,0,0,0-.93,1.61v65.29a.36.36,0,0,0,.62.35l77-44.43a2.1,2.1,0,0,0,.93-1.61V36.46a.5.5,0,0,0-.16-.43.28.28,0,0,0-.13,0A.71.71,0,0,0,212.06,36.11Z"
                                                style="fill: rgb(255, 255, 255); opacity: 0.8; transform-origin: 173.435px 91.9773px;"
                                                class="animable" id="elrlakr3kemdi"></path>
                                        </g>
                                        <circle cx="221.98" cy="130.01" r="1.57"
                                            style="fill: #314A48; transform-origin: 221.98px 130.01px;"
                                            id="elp16za5up9li" class="animable"></circle>
                                        <path
                                            d="M141.83,129.38h-.11a.47.47,0,0,1-.36-.57l.54-2.52c2.23-10.24,5.28-24.27,9.59-26.76,2.31-1.34,3.69.85,4.91,2.77,1.58,2.5,2.69,3.93,4.82,2.7,2.3-1.33,3.67-7.55,5.12-14.13,1.8-8.22,3.85-17.54,8.44-20.19a2.81,2.81,0,0,1,2.63-.25c2.88,1.27,4.33,7.83,5.86,14.78,1.34,6,2.72,12.28,4.85,13.22a1.49,1.49,0,0,0,1.41-.16c2.22-1.29,3.25-5.4,4.25-9.38,1.13-4.5,2.29-9.14,5.26-10.85a1.81,1.81,0,0,1,1.74-.12c2.26,1,3.56,6.88,4.6,12.28l.12.61a.48.48,0,0,1-.95.19l-.12-.62c-.67-3.46-2.05-10.67-4.05-11.58a.89.89,0,0,0-.86.07c-2.61,1.51-3.73,6-4.8,10.25s-2.13,8.49-4.71,10a2.41,2.41,0,0,1-2.28.21c-2.58-1.13-3.95-7.33-5.4-13.89-1.35-6.11-2.88-13-5.31-14.11a1.87,1.87,0,0,0-1.76.21C171,73.94,169,83,167.28,91.07c-1.56,7.09-2.91,13.21-5.57,14.75-3.1,1.79-4.78-.87-6.13-3s-2.25-3.24-3.61-2.46c-3.94,2.28-7.06,16.63-9.12,26.13L142.3,129A.48.48,0,0,1,141.83,129.38Z"
                                            style="fill: #314A48; transform-origin: 173.428px 99.797px;"
                                            id="elvn2rx91cqjk" class="animable"></path>
                                        <path
                                            d="M152.32,98.18a3.56,3.56,0,0,0-1.61,2.79c0,1,.72,1.44,1.61.93a3.55,3.55,0,0,0,1.61-2.79C153.93,98.09,153.21,97.67,152.32,98.18Z"
                                            style="fill: #314A48; transform-origin: 152.32px 100.038px;"
                                            id="elcny19x0e8k" class="animable"></path>
                                        <g id="el7v4ek3be0rp">
                                            <g style="opacity: 0.55; transform-origin: 152.32px 100.038px;"
                                                class="animable" id="el1zno57mdig2">
                                                <path
                                                    d="M152.32,98.18a3.56,3.56,0,0,0-1.61,2.79c0,1,.72,1.44,1.61.93a3.55,3.55,0,0,0,1.61-2.79C153.93,98.09,153.21,97.67,152.32,98.18Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 152.32px 100.038px;"
                                                    id="elm66xxfv9jqc" class="animable"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M175,69.36a3.55,3.55,0,0,0-1.61,2.79c0,1,.72,1.44,1.61.93a3.56,3.56,0,0,0,1.61-2.79C176.64,69.26,175.92,68.85,175,69.36Z"
                                            style="fill: #314A48; transform-origin: 175px 71.2173px;" id="elst02w8vryje"
                                            class="animable"></path>
                                        <g id="elg3z2zrbxi28">
                                            <g style="opacity: 0.55; transform-origin: 175px 71.2173px;"
                                                class="animable" id="el863rw02tazm">
                                                <path
                                                    d="M175,69.36a3.55,3.55,0,0,0-1.61,2.79c0,1,.72,1.44,1.61.93a3.56,3.56,0,0,0,1.61-2.79C176.64,69.26,175.92,68.85,175,69.36Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 175px 71.2173px;"
                                                    id="el7ejhbhr4zn3" class="animable"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M199.39,76.8a3.55,3.55,0,0,0-1.61,2.78c0,1,.72,1.45,1.61.93A3.55,3.55,0,0,0,201,77.73C201,76.7,200.28,76.29,199.39,76.8Z"
                                            style="fill: #314A48; transform-origin: 199.39px 78.6545px;"
                                            id="ell2h3uly1nu" class="animable"></path>
                                        <g id="ela2mdmujcmxd">
                                            <g style="opacity: 0.55; transform-origin: 199.39px 78.6545px;"
                                                class="animable" id="elszfglt9txy">
                                                <path
                                                    d="M199.39,76.8a3.55,3.55,0,0,0-1.61,2.78c0,1,.72,1.45,1.61.93A3.55,3.55,0,0,0,201,77.73C201,76.7,200.28,76.29,199.39,76.8Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 199.39px 78.6545px;"
                                                    id="el202lx7jxz3b" class="animable"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                                <g id="freepik--speech-bubbles--inject-15" class="animable"
                                    style="transform-origin: 243.65px 236.654px;">
                                    <g id="freepik--speech-bubble--inject-15" class="animable"
                                        style="transform-origin: 70.5465px 74.8691px;">
                                        <g id="freepik----inject-15" class="animable"
                                            style="transform-origin: 70.68px 75.105px;">
                                            <path
                                                d="M76.07,71.2a14.66,14.66,0,0,0-3.82.86c-2.81.92-4.11,1.34-4.11-.19a2.43,2.43,0,0,1,.19-.91,5,5,0,0,1,2.18-2.18c1.85-1.08,2.89-.82,3.15.37l5.07-4.33a3,3,0,0,0-3-2.26,8,8,0,0,0-3.16.74V60.21L70,61.73l-1.56.91v3.1c-3.56,2.58-5.78,6-5.78,9.62a5.4,5.4,0,0,0,.32,2c.78,2,2.93,1.69,5.2,1,.79-.24,1.56-.52,2.25-.75,1.62-.53,2.78-.73,2.78.65a3.92,3.92,0,0,1-2.29,3.13c-2.19,1.27-3,.23-3.22-.78l-5.34,4.52a4.77,4.77,0,0,0,.41,1.11,3,3,0,0,0,3.38,1.47,10.1,10.1,0,0,0,2.62-.79V90l4.15-2.39V84.53C76.51,81.88,79,78.32,79,74.59,79,71.91,77.71,71.17,76.07,71.2Z"
                                                style="fill: #314A48; transform-origin: 70.68px 75.105px;"
                                                id="elb3loikfy5q" class="animable"></path>
                                            <g id="elraj4p2as8s">
                                                <path
                                                    d="M76.07,71.2a14.66,14.66,0,0,0-3.82.86c-2.81.92-4.11,1.34-4.11-.19a2.43,2.43,0,0,1,.19-.91,5,5,0,0,1,2.18-2.18c1.85-1.08,2.89-.82,3.15.37l5.07-4.33a3,3,0,0,0-3-2.26,8,8,0,0,0-3.16.74V60.21L70,61.73l-1.56.91v3.1c-3.56,2.58-5.78,6-5.78,9.62a5.4,5.4,0,0,0,.32,2c.78,2,2.93,1.69,5.2,1,.79-.24,1.56-.52,2.25-.75,1.62-.53,2.78-.73,2.78.65a3.92,3.92,0,0,1-2.29,3.13c-2.19,1.27-3,.23-3.22-.78l-5.34,4.52a4.77,4.77,0,0,0,.41,1.11,3,3,0,0,0,3.38,1.47,10.1,10.1,0,0,0,2.62-.79V90l4.15-2.39V84.53C76.51,81.88,79,78.32,79,74.59,79,71.91,77.71,71.17,76.07,71.2Z"
                                                    style="fill: rgb(255, 255, 255); opacity: 0.55; transform-origin: 70.68px 75.105px;"
                                                    class="animable" id="ell07m1v74wi"></path>
                                            </g>
                                        </g>
                                        <g id="freepik--speech-bubble--inject-15" class="animable"
                                            style="transform-origin: 70.5465px 74.8691px;">
                                            <path
                                                d="M54,103.21a1.29,1.29,0,0,1-.67-.18,1.66,1.66,0,0,1-.73-1.53V68.58A4.48,4.48,0,0,1,54.62,65L86.06,46.87a1.49,1.49,0,0,1,2.42,1.39V81.18a4.48,4.48,0,0,1-2.06,3.57L74.48,91.64l-3,8.79a1.16,1.16,0,0,1-2.11.2l-2.61-4.51L55,102.9A2.1,2.1,0,0,1,54,103.21ZM87.08,47.28a1.36,1.36,0,0,0-.66.21L55,65.65a3.8,3.8,0,0,0-1.7,2.93V101.5a1,1,0,0,0,.37.91,1,1,0,0,0,1-.13L67,95.13l3,5.14a.42.42,0,0,0,.43.22.42.42,0,0,0,.38-.29l3.13-9,12.17-7a3.8,3.8,0,0,0,1.7-2.94V48.26a1,1,0,0,0-.37-.9A.62.62,0,0,0,87.08,47.28Z"
                                                style="fill: #314A48; transform-origin: 70.5465px 74.8691px;"
                                                id="el086ev4mmkmqb" class="animable"></path>
                                        </g>
                                    </g>
                                    <g id="freepik--speech-bubble--inject-15" class="animable"
                                        style="transform-origin: 406.303px 410.157px;">
                                        <path
                                            d="M399.47,426.78a6.51,6.51,0,0,1-3.18-.77l-16.9-9.76a2.31,2.31,0,0,1,0-4.32L389.65,406c-.66-1.3-2.44-4.83-2.76-5.54a1.54,1.54,0,0,1,0-1.62,1.39,1.39,0,0,1,1.44-.38l11.08,1.93,10.54-6.09a7,7,0,0,1,6.37,0l16.9,9.75a2.32,2.32,0,0,1,0,4.33L402.65,426A6.51,6.51,0,0,1,399.47,426.78Zm-11.74-27.22s0,.18.17.44c.38.84,3,5.92,3,6l.24.47L380,412.9c-.59.34-.93.77-.93,1.19a1.56,1.56,0,0,0,.93,1.2l16.9,9.75a5.91,5.91,0,0,0,5.25,0l30.52-17.62a1.24,1.24,0,0,0,0-2.39l-16.9-9.75a5.88,5.88,0,0,0-5.25,0l-10.9,6.29-11.47-2A1,1,0,0,0,387.73,399.56Z"
                                            style="fill: #314A48; transform-origin: 406.303px 410.157px;"
                                            id="els413bepdf" class="animable"></path>
                                        <g id="el5vvhag1lt0c">
                                            <g style="opacity: 0.4; transform-origin: 406.303px 410.157px;"
                                                class="animable" id="elnt3lfpmec6m">
                                                <path
                                                    d="M399.47,426.78a6.51,6.51,0,0,1-3.18-.77l-16.9-9.76a2.31,2.31,0,0,1,0-4.32L389.65,406c-.66-1.3-2.44-4.83-2.76-5.54a1.54,1.54,0,0,1,0-1.62,1.39,1.39,0,0,1,1.44-.38l11.08,1.93,10.54-6.09a7,7,0,0,1,6.37,0l16.9,9.75a2.32,2.32,0,0,1,0,4.33L402.65,426A6.51,6.51,0,0,1,399.47,426.78Zm-11.74-27.22s0,.18.17.44c.38.84,3,5.92,3,6l.24.47L380,412.9c-.59.34-.93.77-.93,1.19a1.56,1.56,0,0,0,.93,1.2l16.9,9.75a5.91,5.91,0,0,0,5.25,0l30.52-17.62a1.24,1.24,0,0,0,0-2.39l-16.9-9.75a5.88,5.88,0,0,0-5.25,0l-10.9,6.29-11.47-2A1,1,0,0,0,387.73,399.56Z"
                                                    style="fill: rgb(255, 255, 255); transform-origin: 406.303px 410.157px;"
                                                    id="elna9vse9nrxe" class="animable"></path>
                                            </g>
                                        </g>
                                        <path
                                            d="M400.46,408.17c.4.2.75.38,1,.55s.6.36.93.58a1.71,1.71,0,0,1,.95,1.69,2.85,2.85,0,0,1-1.58,1.75,6.86,6.86,0,0,1-3,.92,5.83,5.83,0,0,1-2.93-.55c-.38-.19-.71-.37-1-.54s-.61-.37-1-.6c-.77-.51-1.11-1.07-1-1.67s.64-1.2,1.64-1.78a6.74,6.74,0,0,1,3.08-.95A5.7,5.7,0,0,1,400.46,408.17Zm-2,3.25a1.65,1.65,0,0,0,.56.17.78.78,0,0,0,.55-.12c.18-.1.25-.2.21-.31a.85.85,0,0,0-.31-.33,13.44,13.44,0,0,0-1.69-1,1.57,1.57,0,0,0-.57-.18.8.8,0,0,0-.54.12c-.18.1-.25.2-.21.31a.72.72,0,0,0,.31.33A15.41,15.41,0,0,0,398.49,411.42Zm4.57-7.29a.56.56,0,0,1,0-.34.71.71,0,0,1,.33-.31l2.12-1.22a1.12,1.12,0,0,1,.46-.14.83.83,0,0,1,.43.1s0,0,.08.07a.21.21,0,0,1,.06.1l3.17,13.43a.53.53,0,0,1,0,.33c0,.1-.14.2-.33.31l-2.12,1.22a.86.86,0,0,1-.46.14.74.74,0,0,1-.43-.09l-.08-.07a.24.24,0,0,1-.06-.11Zm14,2.67c.41.21.76.39,1,.56s.6.36.92.58c.75.51,1.07,1.07,1,1.69s-.64,1.2-1.59,1.75a6.86,6.86,0,0,1-3,.92,5.79,5.79,0,0,1-2.92-.56c-.38-.18-.72-.36-1-.53s-.61-.37-1-.6c-.78-.51-1.12-1.07-1-1.68s.63-1.2,1.63-1.77a6.77,6.77,0,0,1,3.08-1A5.77,5.77,0,0,1,417,406.8Zm-2,3.25a1.57,1.57,0,0,0,.57.18.91.91,0,0,0,.55-.12c.17-.1.24-.2.2-.31a.72.72,0,0,0-.31-.33,15.66,15.66,0,0,0-1.68-1,1.77,1.77,0,0,0-.57-.17.84.84,0,0,0-.55.12c-.18.1-.24.2-.2.31a.75.75,0,0,0,.3.33A14,14,0,0,0,415.07,410.05Z"
                                            style="fill: #314A48; transform-origin: 406.385px 409.971px;"
                                            id="elq7sstkrgp2o" class="animable"></path>
                                    </g>
                                </g>
                                <defs>
                                    <filter id="active" height="200%">
                                        <feMorphology in="SourceAlpha" result="DILATED" operator="dilate" radius="2">
                                        </feMorphology>
                                        <feFlood flood-color="#32DFEC" flood-opacity="1" result="PINK"></feFlood>
                                        <feComposite in="PINK" in2="DILATED" operator="in" result="OUTLINE">
                                        </feComposite>
                                        <feMerge>
                                            <feMergeNode in="OUTLINE"></feMergeNode>
                                            <feMergeNode in="SourceGraphic"></feMergeNode>
                                        </feMerge>
                                    </filter>
                                    <filter id="hover" height="200%">
                                        <feMorphology in="SourceAlpha" result="DILATED" operator="dilate" radius="2">
                                        </feMorphology>
                                        <feFlood flood-color="#ff0000" flood-opacity="0.5" result="PINK"></feFlood>
                                        <feComposite in="PINK" in2="DILATED" operator="in" result="OUTLINE">
                                        </feComposite>
                                        <feMerge>
                                            <feMergeNode in="OUTLINE"></feMergeNode>
                                            <feMergeNode in="SourceGraphic"></feMergeNode>
                                        </feMerge>
                                        <feColorMatrix type="matrix"
                                            values="0   0   0   0   0                0   1   0   0   0                0   0   0   0   0                0   0   0   1   0 ">
                                        </feColorMatrix>
                                    </filter>
                                </defs>
                            </svg>
                        </div>
                    </div>

                </div>

                <!-- Page tableau de bord -->
                <div id="dashboard-page" class="page-content hidden">
                    <div class="empty-state card">
                        <div style="background-color: white; padding: 10px; border-radius: 10px; overflow: hidden;">
                            <iframe title="PFE Analyse pédictive des ventes" width="1050" height="812"
                                src="https://app.powerbi.com/view?r=eyJrIjoiMDUzODk4ZjctNzk1Mi00ZTQ4LThlYWEtOWYyZTQzN2RhYmZmIiwidCI6ImRiZDY2NjRkLTRlYjktNDZlYi05OWQ4LTVjNDNiYTE1M2M2MSIsImMiOjl9&pageName=a05a855f8c40f3dbae90"
                                frameborder="0" allowFullScreen="true"></iframe>

                        </div>
                    </div>
                </div>

                <!-- Page prédiction -->
                <div id="model-content">
                    <!-- Modèle 1: Prédictions du chiffre d'affaires -->
                    <div id="model1-page" class="page-content active">
                        <div class="card p-6 mb-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-primary mb-4">
                                    Prédictions du chiffre d'affaires pour 2025
                                </h3>
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                                    title="Random Forest est un algorithme d'apprentissage automatique utilisant plusieurs arbres de décision pour la classification ou la régression.">
                                    <i class="fas fa-check-circle mr-1"></i> Random Forest
                                </div>
                            </div>

                            <p class="text-secondary mb-4">
                                Ce modèle utilise le modèle <strong class="text-green-800 dark:text-green-300">Random
                                    Forest</strong> pour prédire le chiffre
                                d'affaires annuel basé sur les données historiques de 2023 à février 2025.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="p-4 rounded-lg" style="background-color : #A38C72;">
                                    <p class="text-sm text-gray-600 dark:text-white mb-1">Précision prédictive relative
                                        (R²)</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">99.95 %</p>
                                </div>
                                <div class="p-4 rounded-lg " style="background-color : #6D8C91;">
                                    <p class="text-sm text-gray-600 dark:text-white mb-1">Croissance prévue</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">+8,94 %</p>
                                </div>
                                <div class="p-4 rounded-lg" style="background-color:rgb(144, 99, 115);">
                                    <p class="text-sm text-gray-600 dark:text-white mb-1">Dernière mise à jour des
                                        données</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">04/03/2025</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto border-1 mb-6">
                                    <thead>
                                        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                            style="background-color :rgb(48, 85, 91);">
                                            <th class="p-4 text-left rounded-tl-lg">Année</th>
                                            <th class="p-4 text-left rounded-tr-lg">Chiffre d'affaires (DT)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="financial_results" class="text-sm font-bold text-primary mb-4">
                                        <!-- Contenu dynamique injecté par JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="card p-4 flex flex-col items-center text-center">
                                <p class="text-secondary mb-4">
                                    Voici un histogramme du Chiffre d'affaires annuel de 2023 à 2025, indiquant une
                                    hausse progressive de 6,5 à 7,9 millions des Dinars. 2025 étant une prévision.</p>
                                <img id="financial-plot" src="" alt="Net TTC par année"
                                    class="w-full rounded-lg shadow-md border border-gray-300 dark:border-gray-700"
                                    width="800 px">
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button class="btn-primary py-2 px-4 rounded-lg" onclick="printSection('model1-page')">
                                    <i class="fas fa-download mr-2"></i> Imprimer les données
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Modèle 2: Prédictions Mensuelles de Factures -->
                    <div id="model2-page" class="page-content hidden">
                        <div class="card p-6 mb-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-primary mb-4">
                                    Prédictions Mensuelles de Factures (Mars – Déc 2025)
                                </h3>
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                                    title="SARIMA (Seasonal ARIMA) est un modèle statistique utilisé pour analyser et prévoir des séries temporelles avec des tendances et des saisonnalités.">
                                    <i class="fas fa-chart-line mr-1"></i> Modèle SARIMA
                                </div>
                            </div>

                            <p class="text-secondary mb-4">
                                Ce modèle utilise l’algorithme <strong
                                    class="text-green-800 dark:text-green-300">SARIMA</strong> pour prédire le nombre
                                mensuel de
                                factures en tenant compte des tendances saisonnières.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="p-4 rounded-lg" style="background-color : #A38C72;">
                                    <p class="text-sm text-gray-600 dark:text-white mb-1">Précision relative (MAE)</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">73.33 %</p>
                                </div>
                                <div class="p-4 rounded-lg " style="background-color : #6D8C91;">
                                    <p class="text-sm text-gray-800 dark:text-white mb-1">Croissance totale prévue (Mars
                                        – Déc 2025)</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">31,58 %</p>
                                </div>
                                <div class="p-4 rounded-lg" style="background-color:rgb(144, 99, 115);">
                                    <p class="text-sm text-gray-600 dark:text-white mb-1">Dernière mise à jour des
                                        données</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">04/03/2025</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto border-collapse mb-6">
                                    <thead>
                                        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                            style="background-color :rgb(48, 85, 91);">
                                            <th class="p-4 text-left rounded-tl-lg">Mois</th>
                                            <th class="p-4 text-left rounded-tr-lg">Nombre de Factures</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoice_forecast" class="text-sm font-bold text-primary mb-4">
                                        <!-- Contenu dynamique injecté par JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="card p-4 flex flex-col items-center text-center">
                                <p class="text-secondary mb-4">
                                    Voici une courbe du nombre mensuel de factures de 2023 à 2026, avec des données
                                    historiques jusqu’à février 2025 et des prévisions jusqu’à décembre 2025 .</p>
                                <img id="invoice-plot" src="" alt="Prévision SARIMA du nombre mensuel de factures"
                                    class="w-full rounded-lg shadow-md border border-gray-300 dark:border-gray-700">
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button class="btn-primary py-2 px-4 rounded-lg" onclick="printSection('model2-page')">
                                    <i class="fas fa-download mr-2"></i> Imprimer les données
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Page Support -->
                <div id="support-page" class="page-content hidden">
                    <div class="card p-6 mb-6">
                        <h2 class="text-2xl font-bold text-primary mb-4">Support technique</h2>
                        <p class="text-secondary mb-6">Besoin d'aide ? Notre équipe est là pour vous assister.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="card p-4">
                                <h3 class="font-semibold text-primary mb-3 flex items-center">
                                    <i class="fas fa-question-circle mr-2 text-primary-light"></i>
                                    Centre d'aide
                                </h3>
                                <p class="text-secondary mb-4">Consultez notre base de connaissances pour trouver des
                                    réponses à vos questions.</p>
                                <a href="https://support.example.com/help-center" target="_blank"
                                    class="text-primary-light hover:underline flex items-center">
                                    Accéder au centre d'aide
                                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </a>
                            </div>

                            <div class="card p-4">
                                <h3 class="font-semibold text-primary mb-3 flex items-center">
                                    <i class="fas fa-envelope mr-2 text-primary-light"></i>
                                    Contacter le support
                                </h3>
                                <p class="text-secondary mb-4">Envoyez-nous un message et nous vous répondrons dans les
                                    plus brefs délais.</p>
                                <a href="https://wa.me/21658840064" target="_blank"
                                    class="inline-flex items-center bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                    <i class="fab fa-whatsapp mr-2"></i> Contacter sur WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Formulaire -->
                        <div class="lg:col-span-2">
                            <div class="card p-6 mb-6">
                                <h3 class="font-semibold text-primary mb-4">Créer un ticket de support</h3>
                                <form id="contactForm" action="support.php" method="POST">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-primary mb-1">Nom
                                                complet</label>
                                            <input type="text" id="name" name="name"
                                                class="w-full py-2 px-3 border border-color rounded-lg bg-secondary text-primary focus:outline-none focus:ring-2 focus:ring-primary-light"
                                                required>
                                        </div>
                                        <div>
                                            <label for="email"
                                                class="block text-sm font-medium text-primary mb-1">Email</label>
                                            <input type="email" id="email" name="email"
                                                class="w-full py-2 px-3 border border-color rounded-lg bg-secondary text-primary focus:outline-none focus:ring-2 focus:ring-primary-light"
                                                required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="category"
                                            class="block text-sm font-medium text-primary mb-1">Catégorie</label>
                                        <select id="category" name="category"
                                            class="w-full py-2 px-3 border border-color rounded-lg bg-secondary text-primary focus:outline-none focus:ring-2 focus:ring-primary-light"
                                            required>
                                            <option value="">Sélectionnez une catégorie</option>
                                            <option value="technical">Problème technique</option>
                                            <option value="account">Compte utilisateur</option>
                                            <option value="feature">Demande de fonctionnalité</option>
                                            <option value="feedback">Feedback</option>
                                            <option value="other">Autre</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="message"
                                            class="block text-sm font-medium text-primary mb-1">Sujet</label>
                                        <textarea id="message" name="message" rows="5"
                                            class="w-full py-2 px-3 border border-color rounded-lg bg-secondary text-primary focus:outline-none focus:ring-2 focus:ring-primary-light"
                                            required></textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-primary py-2 px-4 rounded-lg">Envoyer</button>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <!-- Informations de contact -->
                        <div>
                            <div class="card p-6 mb-6">
                                <h3 class="font-semibold text-primary mb-4">Contactez nous</h3>
                                <div class="space-y-4">
                                    <div class="flex items-start">
                                        <div class="bg-primary-light bg-opacity-20 rounded-full p-2 mr-3">
                                            <i class="fas fa-map-marker-alt text-primary-light"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-primary">Adresse</p>
                                            <p class="text-sm text-secondary">Parc Technologique El Ghazela, Ariana 2088
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-primary-light bg-opacity-20 rounded-full p-2 mr-3">
                                            <i class="fas fa-phone text-primary-light"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-primary">Téléphone</p>
                                            <p class="text-sm text-secondary">+216 58 840 064</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="bg-primary-light bg-opacity-20 rounded-full p-2 mr-3">
                                            <i class="fas fa-envelope text-primary-light"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-primary">Email</p>
                                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=admin@beecoders.tn" target="_blank" class="text-sm text-secondary">
                                                admin@beecoders.tn</a>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-primary-light bg-opacity-20 rounded-full p-2 mr-3">
                                            <i class="fas fa-clock text-primary-light"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-primary">Heures d'ouverture</p>
                                            <p class="text-sm text-secondary">Lun-Ven: 9h-17h</p>
                                            <p class="text-sm text-secondary">Sam: 9h-13h</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- page Paramètres -->
                <div id="settings-page" class="page-content hidden">
                    <!-- Formulaire de profil -->
                    <div class="card p-6 mb-6">
                        <h3 class="font-semibold text-primary mb-4">Modifiez vos informations personnelles</h3>

                        <form method="POST" action="update-profile.php" id="updateProfileForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="firstName" class="form-label">Pseudo</label>
                                    <input type="text" id="firstName" name="pseudo" class="form-input"
                                        value="<?= htmlspecialchars($userData['username'] ?? '') ?>"
                                        placeholder="Modifier votre pseudo">
                                </div>

                                <div>
                                    <label for="lastName" class="form-label">Nom complet</label>
                                    <input type="text" id="lastName" name="nom" class="form-input"
                                        value="<?= htmlspecialchars($userData['fullname'] ?? '') ?>"
                                        placeholder="Modifier votre nom et prénom ">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" id="email" name="email" class="form-input"
                                    value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                                    placeholder="Modifier votre adresse email">
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" id="phone" name="phone" class="form-input"
                                    value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                                    placeholder="Modifier votre numéro de téléphone">
                            </div>

                            <div class="mb-4">
                                <label for="dob" class="block text-sm font-medium text-primary mb-1">Date de
                                    naissance</label>
                                <input type="date" id="dob" name="dob" class="form-input"
                                    value="<?= htmlspecialchars($userData['dob'] ?? '') ?>">
                            </div>

                            <div class="mb-4">
                                <label for="currentPassword" class="block text-sm font-medium text-primary mb-1">Mot de
                                    passe
                                    actuel</label>
                                <input type="password" id="currentPassword" name="current_password"
                                    class="form-input pr-10" placeholder="Entrez votre mot de passe actuel">
                            </div>

                            <div class="mb-4">
                                <label for="newPassword" class="block text-sm font-medium text-primary mb-1">Nouveau mot
                                    de
                                    passe</label>
                                <input type="password" id="newPassword" name="new_password" class="form-input pr-10"
                                    placeholder="Entrez votre nouveau mot de passe">
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword"
                                    class="block text-sm font-medium text-primary mb-1">Confirmer le mot de
                                    passe</label>
                                <input type="password" id="confirmPassword" name="confirm_password"
                                    class="form-input pr-10" placeholder="Confirmez votre nouveau mot de passe">
                            </div>

                            <div class="flex justify-end space-x-4 mt-4">
                                <button type="reset" class="btn-secondary py-2 px-6 rounded-lg">Annuler</button>
                                <button type="submit" class="btn-primary py-2 px-6 rounded-lg">Enregistrer les
                                    modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-6 text-center text-sm text-secondary">
                    <p>© 2025 <a href="https://www.beecoders.tn/" target="_blank">BeeCoders</a>. Tous droits réservés.
                    </p>
                </div>
            </main>
        </div>
    </div>

    <!-- Menu de sélection du thème -->

    <div class="fixed bottom-4 right-4 z-50">
        <div class="relative inline-block text-left">
            <button id="themeToggle"
                class="bg-secondary border border-color text-primary py-2 px-4 rounded-lg shadow hover:bg-gray-100 flex items-center space-x-2">
                <span><img src="mode-affchage.png" alt="Mode" style="width: 50px; height: 50px;"></span>
                <!-- <i class="fas fa-chevron-down text-xs"></i> -->
            </button>
            <ul id="themeDropdown"
                class="hidden absolute theme-dropdown right-0 w-36 bg-secondary border border-color rounded-lg shadow-lg text-sm z-50">
                <li><button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-primary"
                        data-theme-value="light">Clair</button></li>
                <li><button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-primary"
                        data-theme-value="dark">Sombre</button></li>
                <li><button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-primary active"
                        data-theme-value="auto">Auto</button></li>
            </ul>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Gestion du thème
        const toggleButton = document.getElementById('themeToggle');
        const dropdownMenu = document.getElementById('themeDropdown');

        toggleButton.addEventListener('click', () => {
            dropdownMenu.classList.toggle('hidden');
        });

        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', (event) => {
            if (!toggleButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        document.querySelectorAll('[data-theme-value]').forEach(button => {
            button.addEventListener('click', () => {
                const theme = button.getAttribute('data-theme-value');
                if (theme === 'auto') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
                } else {
                    document.documentElement.setAttribute('data-bs-theme', theme);
                }
                localStorage.setItem('theme', theme);
                document.querySelectorAll('[data-theme-value]').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                dropdownMenu.classList.add('hidden');
            });
        });

        // Appliquer le thème sauvegardé ou détecter la préférence du système
        const savedTheme = localStorage.getItem('theme') || 'auto';
        if (savedTheme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');

            // Écouter les changements de préférence du système
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (localStorage.getItem('theme') === 'auto') {
                    document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                }
            });
        } else {
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        }

        document.querySelector(`[data-theme-value="${savedTheme}"]`)?.classList.add('active');
        //---------------------------------------------------------------------------------------------------------------

        // Gestion de la sidebar
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const toggleIcon = document.getElementById('toggleIcon');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            sidebar.classList.toggle('sidebar-expanded');

            if (sidebar.classList.contains('sidebar-collapsed')) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                sidebarTexts.forEach(text => text.classList.add('hidden'));
            } else {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
                sidebarTexts.forEach(text => text.classList.remove('hidden'));
            }
        });

        // Menu mobile
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');

        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
        });

        // Gestion du menu déroulant du profil
        const profileDropdownBtn = document.getElementById('profileDropdownBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        profileDropdownBtn.addEventListener('click', () => {
            profileDropdown.classList.toggle('hidden');
        });

        // Fermer le menu du profil si on clique ailleurs
        document.addEventListener('click', (event) => {
            if (!profileDropdownBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.add('hidden');
            }
        });


        // Gestion de la navigation entre les pages
        const navLinks = document.querySelectorAll('.nav-link');
        const pageContents = document.querySelectorAll('.page-content');
        const pageTitle = document.getElementById('pageTitle');

        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();

                // Supprimer la classe 'active' de tous les liens
                navLinks.forEach(navLink => navLink.classList.remove('active'));

                // Ajouter la classe 'active' au lien cliqué
                link.classList.add('active');

                // Masquer toutes les pages et afficher celle correspondante
                const pageName = link.getAttribute('data-page');
                pageContents.forEach(page => page.classList.add('hidden'));
                const activePage = document.getElementById(`${pageName}-page`);
                if (activePage) activePage.classList.remove('hidden');


                // Gérer le titre de la page
                if (["model1", "model2", "model3"].includes(pageName)) {
                    pageTitle.textContent = "Prédiction";
                } else {
                    const titleSpan = link.querySelector('.sidebar-text');
                    pageTitle.textContent = titleSpan ? titleSpan.textContent : link.textContent;
                }

                // Fermer le menu mobile si nécessaire
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('hidden');
                }
            });
        });
        //-------------------------------------------------------------
        //affichage des prédictions
        document.addEventListener('DOMContentLoaded', () => {
            fetch('api.php')
                .then(response => response.json())
                .then(data => {
                    // Sélecteurs des éléments HTML
                    const financialTable = document.getElementById('financial_results');
                    const invoiceTable = document.getElementById('invoice_forecast');
                    const financialPlot = document.getElementById('financial-plot');
                    const invoicePlot = document.getElementById('invoice-plot');

                    // Vérification des données financières
                    if (financialTable && Array.isArray(data.financial_results)) {
                        data.financial_results.forEach(row => {
                            console.log("Row:", row); // Pour débogage

                            const netTTC = row.Net_TTC || "";
                            const cleanedValue = netTTC.match(/[\d.]+/);
                            const netValue = cleanedValue
                                ? parseFloat(cleanedValue[0]).toFixed(2) + ' M DT'
                                : '0.00 M DT';

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
        <td class="p-3 border-b">${row.year}</td>
        <td class="p-3 border-b">${netValue}</td>
    `;
                            financialTable.appendChild(tr);
                        });
                    }

                    // Vérification des prévisions de factures
                    if (invoiceTable && data.invoice_forecast) {
                        invoiceTable.innerHTML = '';
                        data.invoice_forecast.forEach(row => {
                            const tr = document.createElement('tr');
                            const numFactures = parseInt(row.Num_Factures);
                            tr.innerHTML = `
                        <td class="p-3 border-b">${row.Date}</td>
                        <td class="p-3 border-b">${isNaN(numFactures) ? 'N/A' : numFactures}</td>
                    `;
                            invoiceTable.appendChild(tr);
                        });
                    }

                    // Affichage des images (sans dossier "images/")
                    if (Array.isArray(data.images)) {
                        data.images.forEach(img => {
                            if (img === 'net_ttc_2023_2025_prediction.png' && financialPlot) {
                                financialPlot.src = img;
                            } else if (img === 'Prévision SARIMA du nombre mensuel de factures.png' && invoicePlot) {
                                invoicePlot.src = img;
                            }
                        });
                    }

                })
                .catch(error => {
                    console.error('Erreur lors du chargement des données :', error);
                    const financialTable = document.getElementById('financial_results');
                    const invoiceTable = document.getElementById('invoice_forecast');

                    if (financialTable) {
                        financialTable.innerHTML = '<tr><td colspan="2" class="p-3 text-center text-red-500">Erreur lors du chargement des données</td></tr>';
                    }
                    if (invoiceTable) {
                        invoiceTable.innerHTML = '<tr><td colspan="2" class="p-3 text-center text-red-500">Erreur lors du chargement des données</td></tr>';
                    }
                });
        });

        //-------------------------------------------------------------------------------------------------------------------
        // Initialiser l'affichage de la page Accueil si elle est active au départ
        document.addEventListener('DOMContentLoaded', () => {
            const homeLink = document.querySelector('[data-page="home"]');
            homeLink.click();  // Simuler un clic sur "Accueil" pour l'afficher dès le début
        });

        // Enregistrer les modifications du profil
        document.querySelector('#settings-page button').addEventListener('click', function (e) {
            e.preventDefault();

            const newName = document.getElementById('profileName').value;
            const newEmail = document.getElementById('profileEmail').value;

            if (newName) {
                localStorage.setItem('userName', newName);

                // Mettre à jour l'affichage du nom
                const userNameElements = document.querySelectorAll('#userName, #userNameDropdown');
                userNameElements.forEach(element => {
                    element.textContent = newName;
                });

                // Mettre à jour les initiales
                const initials = newName.split(' ').map(name => name[0]).join('').toUpperCase();
                document.getElementById('userInitials').textContent = initials || 'U';
            }

            if (newEmail) {
                localStorage.setItem('userEmail', newEmail);
            }

            // Afficher un message de confirmation
            alert('Modifications enregistrées avec succès !');
        });
        //---------------------------------------------------------------------
    </script>
    <!-- alerte SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- alerte formulaire support -->
    <script>
        document.getElementById('contactForm').addEventListener('submit', async function (e) {
            e.preventDefault(); // Empêche l'envoi classique du formulaire

            const formData = new FormData(this);

            try {
                const response = await fetch('support.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();

                if (result.trim() === 'success') {
                    // Affiche une alerte SweetAlert2 de succès
                    await Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Votre réclamation a été envoyé avec succès !',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#B99644'

                    });

                    // Réinitialise le formulaire
                    this.reset();
                } else {
                    // Affiche une alerte SweetAlert2 d'erreur (réponse du PHP)
                    await Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: result,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#B99644'
                    });
                }
            } catch (error) {
                // Affiche une alerte SweetAlert2 pour les erreurs réseau ou serveur
                await Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur s\'est produite. Veuillez réessayer.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#B99644'
                });
            }
        });
    </script>
    <!-- alerte formulaire modifier profil -->
    <script>
        document.getElementById('updateProfileForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('update-profile.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();

                if (result.trim() === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Profil mis à jour avec succès !',
                        confirmButtonText: 'OK'
                    });
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: result,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Erreur serveur',
                    text: 'Une erreur est survenue. Veuillez réessayer.',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
    <!-- script imprimer prédictions -->
    <script>
        function printSection(sectionId) {
            const content = document.getElementById(sectionId).innerHTML;
            const printWindow = window.open('', '', 'width=1000,height=700');
            printWindow.document.write(`
      <html>
        <head>
          <title>Export PDF</title>
          <style>
            body { font-family: sans-serif; padding: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ccc; padding: 8px; }
          </style>
        </head>
        <body>${content}</body>
      </html>
    `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>

    <script>(function () { function c() { var b = a.contentDocument || a.contentWindow.document; if (b) { var d = b.createElement('script'); d.innerHTML = "window.__CF$cv$params={r:'93d8e7cd046a777e',t:'MTc0Njg3NDUzOS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);"; b.getElementsByTagName('head')[0].appendChild(d) } } if (document.body) { var a = document.createElement('iframe'); a.height = 1; a.width = 1; a.style.position = 'absolute'; a.style.top = 0; a.style.left = 0; a.style.border = 'none'; a.style.visibility = 'hidden'; document.body.appendChild(a); if ('loading' !== document.readyState) c(); else if (window.addEventListener) document.addEventListener('DOMContentLoaded', c); else { var e = document.onreadystatechange || function () { }; document.onreadystatechange = function (b) { e(b); 'loading' !== document.readyState && (document.onreadystatechange = e, c()) } } } })();</script>


</body>

</html>