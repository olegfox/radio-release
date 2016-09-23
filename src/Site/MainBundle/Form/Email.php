<?php

namespace Site\MainBundle\Form;

class Email
{

    /**
     * Имя
     *
     * @var
     */
    private $name;

    /**
     * Email
     *
     * @var
     */
    private $email;

    /**
     * Сообщение
     *
     * @var
     */
    private $message;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

}
