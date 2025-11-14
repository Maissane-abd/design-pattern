<?php

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
    private PDO $db;

    public function __construct()
    {
        $this->db = new PDO('mysql:host=localhost;dbname=test', 'root', '');

    }

    public function addUser(string $name, string $email, string $phone): void
    {
        if (empty($name) || empty($email)) {
            throw new Exception("Nom et email requis");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        //

        $user = new User($name, $email, $phone);
        $this->users[] = $user;

        $stmt = $this->db->prepare("INSERT INTO users (name, email, phone) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $phone]);
    }

    public function notifyUser(User $user, string $message, string $type): void
    {
        if ($type === 'email') {
            mail($user->email, "Notification", $message);
            echo "Email envoyé à {$user->email}\n";
        } elseif ($type === 'sms') {
            echo "SMS envoyé au {$user->phone}: {$message}\n";
        } elseif ($type === 'push') {
            echo "Notification push envoyée à {$user->name}: {$message}\n";
        } elseif ($type === 'slack') {
            $ch = curl_init('https://slack.com/api/chat.postMessage');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $message]));
            curl_exec($ch);
            curl_close($ch);
            echo "Message Slack envoyé\n";
        }

        $stmt = $this->db->prepare("INSERT INTO notifications (user_email, message, type, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user->email, $message, $type]);
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
        return json_encode($this->users);
    }

    public function exportToCsv(): string
    {
        $csv = "nom,email,phone\n";
        foreach ($this->users as $user) {
            $csv .= "{$user->name},{$user->email},{$user->phone}\n";
        }
        return $csv;
    }

    public function calculateDiscount(User $user, string $membershipType): float
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
$manager->notifyUser($user, "Bienvenue!", "email");
echo $manager->generateReport();

