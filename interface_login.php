<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système d'information BTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-indigo-600 py-6 px-8">
                <h1 class="text-white text-2xl font-bold text-center">Système d'information BTS</h1>
            </div>
                        <div class="p-8">
                <h2 class="text-xl text-gray-700 font-semibold mb-6 text-center">Connexion</h2>
                
                <form action="login.php" method="post">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Identifiant</label>
                        <input type="text" id="username" name="username" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Votre identifiant" required>
                    </div>
                    
                    <!-- Champ mot de passe -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" id="password" name="password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Votre mot de passe" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="account-type" class="block text-sm font-medium text-gray-700 mb-1">Type de compte</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-md p-3 cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-colors flex flex-col items-center group account-option" data-type="user">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Utilisateur</span>
                            </div>
                            <div class="border border-gray-200 rounded-md p-3 cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-colors flex flex-col items-center group account-option" data-type="admin">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Administrateur</span>
                            </div>
                        </div>
                        <input type="hidden" id="account-type" name="account-type" value="user">
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-md">
                            Se connecter
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>© 2025 Système d'information BTS - Tous droits réservés</p>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.account-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.account-option').forEach(el => {
                    el.classList.remove('bg-indigo-50', 'border-indigo-500');
                    el.classList.add('border-gray-200');
                });
                
                this.classList.remove('border-gray-200');
                this.classList.add('bg-indigo-50', 'border-indigo-500');
                
                document.getElementById('account-type').value = this.dataset.type;
            });
        });
         document.querySelector('[data-type="user"]').click();
    </script>
</body>
</html>
