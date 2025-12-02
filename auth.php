<?php
session_start();
require_once 'config.php'; // Assurez-vous que ce fichier existe avec vos infos DB

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- INSCRIPTION ---
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $username = trim($_POST['nom_utilisateur']);
        $email = trim($_POST['email']);
        $password = $_POST['mot_de_passe'];

        if (!empty($username) && !empty($email) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR nom_utilisateur = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Erreur : Cet email ou nom d'utilisateur est déjà pris.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe) VALUES (?, ?, ?)");
                if ($insert->execute([$username, $email, $passwordHash])) {
                    $message = "Succès : Inscription réussie ! Connectez-vous.";
                } else {
                    $message = "Erreur technique lors de l'inscription.";
                }
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    }

    // --- CONNEXION ---
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $identifiant = trim($_POST['identifiant']);
        $password = $_POST['mot_de_passe'];

        if (!empty($identifiant) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? OR nom_utilisateur = ?");
            $stmt->execute([$identifiant, $identifiant]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nom_utilisateur'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $message = "Erreur : Identifiant ou mot de passe incorrect.";
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification Météo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            /* Fond dégradé moderne si l'image ne charge pas, sinon votre gif */
            
            background-image: url('back-form.gif');
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .tab-btn.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-bottom: 2px solid white;
        }
    </style>
</head>
<body class="p-4 text-white">
    <div class="glass-container w-full max-w-md p-8 rounded-2xl">
        <div class="flex mb-8 border-b border-white border-opacity-20">
            <button id="login-tab" class="tab-btn active w-1/2 py-3 text-lg font-semibold transition">Connexion</button>
            <button id="register-tab" class="tab-btn w-1/2 py-3 text-lg font-semibold transition">Inscription</button>
        </div>

        <form id="login-form" class="space-y-6" method="POST" action="">
            <input type="hidden" name="action" value="login"> <h2 class="text-3xl font-bold text-center">Bon retour !</h2>
            
            <div>
                <label class="block text-sm font-medium mb-2 pl-1">Identifiant</label>
                <input type="text" name="identifiant" placeholder="Email ou Pseudo" required
                       class="w-full px-4 py-3 rounded-xl bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:bg-opacity-20 transition placeholder-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 pl-1">Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="••••••••" required
                       class="w-full px-4 py-3 rounded-xl bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:bg-opacity-20 transition placeholder-gray-300">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 rounded-xl font-bold shadow-lg transform hover:scale-[1.02] transition">
                Se Connecter
            </button>
        </form>

        <form id="register-form" class="space-y-6 hidden" method="POST" action="">
            <input type="hidden" name="action" value="register"> <h2 class="text-3xl font-bold text-center">Créer un compte</h2>
            
            <div>
                <label class="block text-sm font-medium mb-2 pl-1">Pseudo</label>
                <input type="text" name="nom_utilisateur" placeholder="Votre pseudo" required
                       class="w-full px-4 py-3 rounded-xl bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:bg-opacity-20 transition placeholder-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 pl-1">Email</label>
                <input type="email" name="email" placeholder="exemple@mail.com" required
                       class="w-full px-4 py-3 rounded-xl bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:bg-opacity-20 transition placeholder-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 pl-1">Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="••••••••" required
                       class="w-full px-4 py-3 rounded-xl bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:bg-opacity-20 transition placeholder-gray-300">
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-500 hover:bg-emerald-400 rounded-xl font-bold shadow-lg transform hover:scale-[1.02] transition">
                S'inscrire
            </button>
        </form>
        
        <?php if (!empty($message)): ?>
            <div class="mt-6 p-3 rounded-lg bg-white bg-opacity-20 text-center font-semibold border border-white border-opacity-30">
                <?= $message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const loginTab = document.getElementById('login-tab');
        const registerTab = document.getElementById('register-tab');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        function toggleForm(showLogin) {
            if(showLogin) {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                loginTab.classList.remove('active');
                registerTab.classList.add('active');
            }
        }

        loginTab.addEventListener('click', () => toggleForm(true));
        registerTab.addEventListener('click', () => toggleForm(false));
    </script>
</body>
</html>