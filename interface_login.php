<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système d'information BTS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#1a4d85',
                            light: '#e6f0ff',
                            dark: '#143d6b'
                        }
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-3 font-inter text-gray-800">
    <div class="w-full max-w-sm">
        <!-- Logo centré au-dessus -->
        <div class="text-center mb-6">
            <img src="logo.png" alt="Logo BTS" class="mx-auto h-24 w-auto">
        </div>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-primary py-3 px-4">
                <h1 class="text-white text-xl font-semibold text-center">Système d'information BTS</h1>
            </div>
            <div class="px-5 py-4">
                <form action="login.php" method="post" class="space-y-3">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Identifiant</label>
                        <input type="text" id="username" name="username" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                               placeholder="Votre identifiant" required>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" id="password" name="password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                               placeholder="Votre mot de passe" required>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-primary text-white py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-1 focus:ring-primary text-sm font-medium">
                        Se connecter
                    </button>
                </form>
                
                <div class="mt-4 flex justify-between items-center">
                    <a href="index.php" class="flex items-center text-sm text-primary hover:text-primary-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Retour à l'accueil
                    </a>
                    
                    <a href="mailto:elias.gauthier@lp2i-poitiers.fr?subject=Mot%20de%20passe%20oublié" class="text-xs text-primary hover:text-primary-dark hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-3 text-center text-xs text-gray-500">
            <p>© 2025 Système d'information BTS</p>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.account-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.account-option').forEach(el => {
                    el.classList.remove('bg-primary-light', 'border-primary', 'text-primary');
                    el.classList.add('border-gray-300', 'text-gray-700');
                });
                
                this.classList.remove('border-gray-300', 'text-gray-700');
                this.classList.add('bg-primary-light', 'border-primary', 'text-primary');
                
                document.getElementById('account-type').value = this.dataset.type;
            });
        });
        document.querySelector('[data-type="user"]').click();
    </script>
</body>
</html>
