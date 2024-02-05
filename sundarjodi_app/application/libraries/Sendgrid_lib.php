<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sendgrid_lib{

    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->config->load('sendgrid', true);
    }

    public function sendEmail($to, $subject, $message)
    {
        $this->ci->load->library('sendgrid');

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("help@sundarjodi.com", "SundarJodi");
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/plain", $message);

        $sendgrid = new \SendGrid($this->ci->config->item('sendgrid_api_key', 'sendgrid'));

        try {
            $response = $sendgrid->send($email);
            return $response->statusCode() == 202;
        } catch (Exception $e) {
            log_message('error', 'SendGrid Error: ' . $e->getMessage());
            return false;
        }
    }
}

