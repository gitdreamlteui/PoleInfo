<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
    
    <!-- Barre de navigation -->
    <header class="bg-indigo-600 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0 shadow-md">
        <h1 class="text-xl font-bold">Système d'information BTS - Administration</h1>
        <a href="../index.php">
            <button class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-md font-semibold">
                Tableau d'accueil
            </button>
        </a>
        <a href="../user/dashboard.php">
            <button class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-md font-semibold">
                Ajout Réservation
            </button>
        </a>
        <a href="../user/logout.php">
        <button class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-md font-semibold">
            Déconnexion
        </button>
        </a>
    </header>
    <div class="w-full px-4 mt-32 flex justify-around">

        <div class="w-full px-4 my-10 flex justify-center">
            <div class="bg-white shadow-lg p-6 rounded-lg w-1/2">
            <h2 class="text-2xl font-bold mb-4">Ajouts d'Utilisateurs</h2>
            <form class="space-y-4">
                <input type="text" placeholder="Prénom" class="w-full p-2 border rounded-md">
                <input type="text" placeholder="Nom" class="w-full p-2 border rounded-md">
                <input type="password" placeholder="Mot de passe" class="w-full p-2 border rounded-md">
                <select name="choix_droit" class="w-full p-2 border rounded-md">
                    <option value="Administrateur">Administrateur</option>
                    <option value="Utilisateur">Utilisateur</option>
                </select>
                <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Créer Utilisateur</button>
            </form>
            </div>
        </div>
        <div class="w-full px-4 my-10 flex justify-center">
            <div class="bg-white shadow-lg p-6 rounded-lg w-1/2">
            <h2 class="text-2xl font-bold mb-4">Suppressions d'Utilisateurs</h2>
            <form class="space-y-4 flex flex-col justify-center">
                <select name="sup_user" class="w-full p-2 border rounded-md">
                    <option value=""></option>
                </select>
                <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Utilisateur</button>
            </form>
            </div>
        </div>
    </div>
        
    <div class="w-full px-4 mt-10">
        <div class="bg-indigo-600 text-white text-xl font-bold p-4 rounded-lg mb-6 shadow-lg">
            Gestion des ressources
        </div>
        <div class="grid grid-cols-4 gap-4">
            <!-- Gestion des Matières -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Matières</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom de la matière" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Matière</button>
                </form>
                <form class="mt-10 space-y-4">
                <select name="sup_matiere" class="w-full p-2 border rounded-md">
                    <option value=""></option>
                </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Matière</button>
                </form>
            </div>
            
            <!-- Gestion des Créneaux -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Créneaux</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="heure" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Créneau</button>
                </form>
                <form class="mt-10 space-y-4">
                <select name="creneau" class="w-full p-2 border rounded-md">
                    <option value=""></option>
                </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Créneau</button>
                </form>
            </div>
            
            <!-- Gestion des Classes -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Classes</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom de la classe" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Classe</button>
                </form>
                <form class="mt-10 space-y-4">
                <select name="sup_classe" class="w-full p-2 border rounded-md">
                    <option value=""></option>
                </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Classe</button>
                </form>
            </div>
            
            <!-- Gestion des Salles -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Salles</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom ou numéro de la salle" class="w-full p-2 border rounded-md">
                    <input type="text" placeholder="Types de salles : TP-info, Cours,TP-physiqe,etc.." class="w-full p-2 border rounded-md">
                    <input type="number" placeholder="Capacité de la salle : 0-99" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Salle</button>
                </form>
                <form class="mt-10 space-y-4">  
                <select name="sup_salle" class="w-full p-2 border rounded-md">
                    <option value=""></option>
                </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Salle</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="mt-6 text-center text-sm text-gray-500">
        <p>© 2025 Système d'information BTS - Tous droits réservés</p>
    </div>
</body>
</html>