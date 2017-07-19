<?php
namespace AppBundle\Services;

class MailService {

    /**
     * admin email address
     */
    const EMAIL_FROM = 'example@abc.com';

    /**
     * service container
     *
     * @var object
     */
    protected $service;

    /**
     * init service
     *
     * @param object $service
     * @return $this
     */
    public function __construct($service) {
        $this->service = $service;

    }

    function send_mail($subject, $to, $body){
        //send mail that user valid
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(self::EMAIL_FROM)
            ->setTo($to)
            ->setBody( $body, 'text/html');

        return $this->service->get('mailer')->send($message);
    }
}