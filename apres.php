<?php

# S : Single Responsibility Principle -> Une classe doit avoir une seule responsabilité.
# O : Open/Closed Principle -> Une classe doit être ouverte à l'extension mais fermée à la modification.
# L : Liskov Substitution Principle -> Les objets d'une classe dérivée doivent pouvoir remplacer les objets de la classe de base sans altérer le fonctionnement du programme.
# I : Interface Segregation Principle -> Les clients ne doivent pas être forcés de dépendre d'interfaces qu'ils n'utilisent pas.
# D : Dependency Inversion Principle -> Les modules de haut niveau ne doivent pas dépendre des modules de bas niveau. Les deux doivent dépendre d'abstractions.

class User
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone
    ) {}
}

class UserManager
{
    private array $users = [];
   
    public function addUser(string $name, string $email, string $phone): void
    {
        if (empty($name) || empty($email)) {
            throw new Exception("Nom et email requis");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        $user = new User($name, $email, $phone);
        $this->users[] = $user;
    }
}

class ManagerNotification 
{
    public function send(string $to, string $message, string $type): void
    {
        if ($type === 'email') {
            mail($to, "Notification", $message);
            echo "Email envoyé à {$to}\n";
        } elseif ($type === 'sms') {
            echo "SMS envoyé au {$to}: {$message}\n";
        } elseif ($type === 'push') {
            echo "Notification push envoyée à {$to}: {$message}\n";
        } elseif ($type === 'slack') {
            $ch = curl_init('https://slack.com/api/chat.postMessage');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $message]));
            curl_exec($ch);
            curl_close($ch);
            echo "Message Slack envoyé\n";
        }
    }
}

class ManagerDatabase
{
    private PDO $db;

    public function __construct()
    {
        $this->db = new PDO('mysql:host=localhost;dbname=test', 'root', '');

    }

    public function saveNotification(string $email, string $message, string $type): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_email, message, type, sent_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$email, $message, $type]);
    }

    public function saveUser(User $user): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, phone) VALUES (?, ?, ?)"
        );
        $stmt->execute([$user->name, $user->email, $user->phone]);
    }
}

class Report 
{
    private array $users;
    
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    public function generateReport(): string
    {
        $report = "=== RAPPORT DES UTILISATEURS ===\n";
        foreach ($this->users as $user) {
            $report .= "Nom: {$user->name}, Email: {$user->email}\n";
        }
        return $report;
    }

    public function exportToJson(): string
    {
        return json_encode($this->users, JSON_THROW_ON_ERROR);
    }

    public function exportToCsv(): string
    {
        $csv = "nom,email,phone\n";
        foreach ($this->users as $user) {
            $csv .= "{$user->name},{$user->email},{$user->phone}\n";
        }
        return $csv;
    }
}

class Discount 
{
    public function calculateDiscount(string $membershipType): float
    {
        if ($membershipType === 'premium') {
            return 0.20;
        } elseif ($membershipType === 'gold') {
            return 0.15;
        } else {
            return 0.05;
        }
    }
}

$manager = new UserManager();
$manager->addUser("Alice", "alice@example.com", "+33612345678");

$user = new User("Alice", "alice@example.com", "+33612345678");
$database = new ManagerDatabase();
$database->saveUser($user);