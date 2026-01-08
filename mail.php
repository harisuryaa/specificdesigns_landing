<?php

class ContactForm {
    private $recipient;
    private $fromName;
    private $fromEmail;

    public function __construct($recipient, $fromName, $fromEmail) {
        $this->recipient = $recipient;
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;
    }

    public function sendEmail($name, $email, $subject, $phone, $message, $company, $budget, $solution) {
        $email_content = $this->buildEmailContent($name, $email, $subject, $phone, $message, $company, $budget, $solution);
        $email_headers = $this->buildEmailHeaders();

        if (mail($this->recipient, $subject, $email_content, $email_headers)) {
            http_response_code(200);
            echo "Thank You! Your message has been sent.";
        } else {
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send your message.";
        }
    }

    private function buildEmailContent($name, $email, $subject, $phone, $message, $company, $budget, $solution) {
        $content = "";
        $fields = array(
            "Name" => $name,
            "Email" => $email,
            "Phone" => $phone,
            "Company" => $company,
            "Budget Range" => $budget,
            "Solution Needed" => $solution,
            "Message" => $message
        );
        foreach ($fields as $fieldName => $fieldValue) {
            if (!empty($fieldValue)) {
                $content .= "$fieldName: $fieldValue \r\n\n";
            }
        }
        return $content;
    }

    private function buildEmailHeaders() {
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        return $headers;
    }
}

// CONFIGURATION
$recipient = "hariandprojects@gmail.com";
$fromName = "SpecificDesigns Contact";
// Ensure this email domain matches your hosting to prevent spam flagging
$fromEmail = "noreply@specificdesigns.in";

$contactForm = new ContactForm($recipient, $fromName, $fromEmail);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture the correct HTML input names
    $name = strip_tags(trim($_POST["name"])); // Changed from fname/lname
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST["phone"]); // Changed from tel

    // 2. Capture extra fields present in your HTML
    $company = isset($_POST["company"]) ? trim($_POST["company"]) : "";
    $solution = isset($_POST["solution"]) ? trim($_POST["solution"]) : "";
    $budgetMap = [
        "1" => "5,000 - 10,000",
        "2" => "10,000 - 15,000",
        "3" => "15,000 - 20,000",
        "4" => "20,000 - 25,000",
        "5" => "25,000 - Above"
    ];
    $budgetVal = isset($_POST["Budget"]) ? $_POST["Budget"] : "";
    $budget = isset($budgetMap[$budgetVal]) ? $budgetMap[$budgetVal] : "Not Selected";

    $message = trim($_POST["message"]); // Changed from textarea

    // Create a subject line since HTML doesn't provide one
    $subject = "New Inquiry from $name ($company)";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Please provide a valid email address.";
        exit;
    }

    $contactForm->sendEmail($name, $email, $subject, $phone, $message, $company, $budget, $solution);
} else {
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}
?>