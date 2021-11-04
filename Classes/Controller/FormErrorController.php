<?php

namespace Priorist\EdmTypo3\Controller;

class FormErrorController extends AbstractController
{
  public function sendErrorMessageAction() {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $settings = $this->settings;

    // Set mail recipient
    $to = $settings['errors']['enrollment']['mailTo'];

    // Set mail subject
    $subject = $settings['errors']['enrollment']['subject'];

    // Set mail headers
    $headers = "From: " . $settings['errors']['enrollment']['mailFrom'] . "\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/html; charset=\"UTF-8\"\n";

    // https://stackoverflow.com/a/4169652
    $headers .= "X-Priority: 1 (Highest)\n";
    $headers .= "X-MSMail-Priority: High\n";
    $headers .= "Importance: High\n";

    // Set mail message
    $message = "<html><body>";
    $message .= "<h1>Timestamps</h1>";
    $message .= "<ul><li>Request Started: " . $data['requestStartedTimestamp'] . "</li>";
    $message .= "<li>Request Failed: " . $data['requestFailedTimestamp'] . "</li>";
    $message .= "<h1>Error Message</h1>";
    $message .= $this->getNestedList($data['errorMessage']);
    $message .= "<h1>Form Values</h1>";
    $message .= $this->getNestedList($data['values']);
    $message .= "</body></html>";

    // Send mail
    mail($to, $subject, $message, $headers);
  }

  private function getNestedList($arr) {
    $message = "<ul>";
    foreach ($arr as $key => $value) {
      if (!is_array($value)) {
        $message .= "<li><strong>" . $key . ":</strong> " . $value . "</li>";
      } else {
        $message .= "<li><strong>" . $key . "</strong>\n";
        $message .= "<ul>";
        foreach ($arr[$key] as $key2 => $value2) {
          if (!is_array($value2)) {
            $message .= "<li><strong>" . $key2 . ":</strong> " . $value2 . "</li>";
          } else {
            $message .= "<li><strong>" . $key2 . "</strong>\n";
            $message .= "<ul>";
            foreach ($arr[$key][$key2] as $key3 => $value3) {
              $message .= "<li><strong>" . $key3 . ":</strong> " . $value3 . "</li>";
            }
            $message .= "</ul></li>";
          }
        }
        $message .= "</ul></li>";
      }
    };
    $message .= "</ul>";

    return $message;
  }
}