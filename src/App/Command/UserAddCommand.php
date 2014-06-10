<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

/**
 * Class UserAddCommand
 * @package App\Command
 */
class UserAddCommand extends \Knp\Command\Command
{
    /**
     * {inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:add')
            ->setDescription('Add a user');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $dialog DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');
        $app = $this->getSilexApplication();

        $validator = function ($value) {
            if (trim($value) == '') {
                throw new \Exception('You must provide a value !');
            }
            return $value;
        };

        // username, password, email
        $username = $dialog->askAndValidate($output, "Username :", $validator, false);
        $password = $dialog->askAndValidate($output, 'Password :', $validator, false);
        $email = $dialog->askAndValidate($output, 'Email :', $validator, false);

        // Other collumn
        $firstname = $dialog->askAndValidate($output, 'First Name :', $validator, false);
        $lastname = $dialog->askAndValidate($output, 'Last Name :', $validator, false);
        $phone = $dialog->askAndValidate($output, 'Phone :', $validator, false);
        
        // role
        $roles = array_keys($app['security.role_hierarchy']);
        array_unshift($roles, 'ROLE_USER');
        $roles = array_unique($roles);

        $selected = $dialog->select($output, 'Role(s)', $roles, 0, false, 'The value "%s" is not valid', true);
        $selectedRoles = array_map(function($r) use ($roles) {
            return $roles[$r];
        }, $selected);

        // user
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPhone($phone);
        $user->setActive(1);
        if($selectedRoles) {
            $user->setRoles($selectedRoles);
        }

        // password
        $encoderFactory = $app['security.encoder_factory'];
        $encoder = $encoderFactory->getEncoder($user);
        $password = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($password);

        // persist
        $em = $app["orm.em"];
        $em->persist($user);
        $em->flush();

        $output->writeln(sprintf("The user %s has been created and activated.", $username));
    }
}
