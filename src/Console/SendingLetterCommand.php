<?php
/*
 * @package     Abandoned Cart Plugin
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2023 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

namespace Joomla\Plugin\RadicalMart\AbandonedCart\Console;

use Exception;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendingLetterCommand extends AbstractCommand
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;

	/**
	 * The default command name
	 *
	 * @var    string|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'radicalmart:sendingletter:abandonedcart';

	/**
	 * The output to command style.
	 *
	 * @var   SymfonyStyle
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected SymfonyStyle $ioStyle;

	/**
	 *  The input to inject into the command.
	 *
	 * @var   InputInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected InputInterface $cliInput;

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @throws Exception
	 *
	 * @return  integer  The command exit code.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		// Configure the Symfony output helper
		$this->configureSymfonyIO($input, $output);

		return 0;
	}

	/**
	 * Configure the command.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$this->setDescription('sending notifications by email');

		$help = "<info>%command.name%</info> sending notifications by email.
    \nUsage: <info>php %command.full_name% [flags]</info>";

		$this->setHelp($help);
	}

	/**
	 * Configure the IO.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function configureSymfonyIO(InputInterface $input, OutputInterface $output)
	{
		$this->cliInput = $input;
		$this->ioStyle  = new SymfonyStyle($input, $output);
	}
}