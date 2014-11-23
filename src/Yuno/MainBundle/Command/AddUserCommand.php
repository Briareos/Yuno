<?php

namespace Yuno\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;
use Yuno\MainBundle\Entity\User;

class AddUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('yuno:user:create')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $user->setUsername($input->getArgument('username'));
        $user->setEmail($input->getArgument('email'));
        $user->setPlainPassword($input->getArgument('password'));
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        $violations = $this->getValidator()->validate($user);

        if ($violations->count()) {
            $errors = array_reduce((array) $violations->getIterator(), function ($errors, ConstraintViolation $violation) {
                $errors[] = sprintf('<info>[%s]</info>: %s', $violation->getPropertyPath(), $violation->getMessage());

                return $errors;
            }, []);
            $output->writeln("<error>Errors:</error>\n".implode("\n", $errors));

            return 1;
        }

        $this->getEm()->persist($user);
        $this->getEm()->flush();

        $output->writeln(sprintf('User <info>%s</info> successfully created', $user->getUsername()));

        return 0;
    }

    /**
     * @return EntityManager
     */
    private function getEm()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @return Validator
     */
    private function getValidator()
    {
        return $this->getContainer()->get('validator');
    }
}