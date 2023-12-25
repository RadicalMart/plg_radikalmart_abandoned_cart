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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalMart\Administrator\Helper\LayoutsHelper;
use Joomla\Component\RadicalMart\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalMart\Administrator\Model\CartModel;
use Joomla\Component\RadicalMart\Administrator\Model\CustomerModel;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
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

		$this->uriReinitialize();

		$this->scanAbandonedCarts();


		return 0;
	}

	/**
	 * Method to reinitialize Joomla Uri for site.
	 *
	 * @param   bool  $force  Force reinitialize.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function uriReinitialize(bool $force = false)
	{
		if ($this->uriReInstance === true && $force === false)
		{
			return;
		}

		$source = [
			'PHP_SELF'    => $_SERVER['PHP_SELF'],
			'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
		];

		$_SERVER['PHP_SELF']    = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		Uri::reset();
		Uri::getInstance();
		Uri::root();
		Uri::base();
		Uri::current();
		$this->uriReInstance = true;

		$_SERVER['PHP_SELF']    = $source['PHP_SELF'];
		$_SERVER['SCRIPT_NAME'] = $source['SCRIPT_NAME'];
	}

	protected function scanAbandonedCarts()
	{
		$io = $this->ioStyle;
		$io->title('Scan abandoned carts');

		$io->text('Calculate total carts');
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->quoteName('#__radicalmart_carts'));
		$total = $db->setQuery($query)->loadResult();
		$io->progressStart(1);
		$io->progressFinish();

		$io->title('Total id');
		$io->progressStart($total);

		$this->recursiveScanCarts();

		$io->progressFinish();
	}

	protected function recursiveScanCarts(int $last = 0)
	{
		$limit = 10;

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__radicalmart_carts'))
			->where($db->quoteName('id') . ' > :last_id')
			->bind(':last_id', $last, ParameterType::INTEGER)
			->order('id');
		$pks   = $db->setQuery($query, 0, $limit)->loadColumn();
		$total = (count($pks));

		if ($total === 0)
		{
			return;
		}

		$language = Factory::getApplication()->getLanguage();
		$language->load('com_radicalmart', JPATH_ADMINISTRATOR, 'ru-RU', true);
		$language->load('plg_radicalmart_abandoned_cart', JPATH_ADMINISTRATOR, 'ru-RU', true);

		$pks = ArrayHelper::toInteger($pks);

		$params = ParamsHelper::getComponentParams();
		$timeout = (int) $params->get('abandoned_cart_timeout', 7);
		$timeout = (new Date('-' . $timeout . 'days'))->toUnix();

		$subject = Text::_($params->get('abandoned_cart_subject', 'PLG_RADICALMART_ABANDONED_CART_SUBJECT'));

		foreach ($pks as $pk)
		{
			$last = $pk;

			/** @var CartModel $model */
			$model = $this->getMVCFactory()->createModel('Cart', 'Administrator',
				['ignore_request' => true]);

			$cart = $model->getItem($pk);
			if (empty($cart->data['user_id']))
			{
				$this->ioStyle->progressAdvance();

				continue;
			}

			$customerId = (int) $cart->data['user_id'];
			/** @var CustomerModel $model */
			$customerModel = $this->getMVCFactory()->createModel('Customer', 'Administrator',
				['ignore_request' => true]);

			$customer = $customerModel->getItem($customerId);
			if (empty($customer->contacts))
			{
				$this->ioStyle->progressAdvance();

				continue;
			}

			$email = false;
			if (!empty($customer->contacts['email']))
			{
				$email = $customer->contacts['email'];
			}

			elseif (!empty($customer->user['email']))
			{
				$email = $customer->user['email'];
			}

			if (empty($email))
			{
				$this->ioStyle->progressAdvance();

				continue;
			}

			if ($email !== 'AlexLialin@mail.ru')
			{
				continue;
			}

			$date = $cart->data['modified'];

			if (!empty($cart->data['plugins']['abandoned_cart']))
			{
				$date = $cart->data['plugins']['abandoned_cart'];
			}

			/*
			 * Сегодня 15 число
			 * Корзина 12 число
			 * timeout 8 число
			 */
			$date = (new Date($date))->toUnix();

			if ($date > $timeout)
			{
				continue;
			}

			$html = LayoutsHelper::renderSiteLayout('plugins.radicalmart.abandoned_cart.radicalmart.email',
				[
					'cart'     => $cart,
					'customer' => $customer
				]
			);

			$this->sendEmail($subject, $email, $html);

			$query  = $db->getQuery(true)
				->select(['id', 'plugins'])
				->from($db->quoteName('#__radicalmart_carts'))
				->where($db->quoteName('id') . ' = :cart_id')
				->bind(':cart_id', $pk, ParameterType::INTEGER);
			$update = $db->setQuery($query, 0, 1)->loadObject();

			$update->plugins = new Registry($update->plugins);
			$update->plugins->set('abandoned_cart', Factory::getDate()->toUnix());
			$update->plugins = $update->plugins->toString();
			$db->updateObject('#__radicalmart_carts', $update, 'id');

			$this->ioStyle->progressAdvance();
		}

		// Clean RAM
		$db->disconnect();

		if ($total === $limit)
		{
			$this->recursiveScanCarts($last);
		}
	}

	protected function sendEmail(string $subject, $recipient, string $body, int $timeout = 15): bool
	{
		if (empty($body))
		{
			throw new \Exception('empty body');
		}

		// Check recipients
		if (is_array($recipient))
		{
			foreach ($recipient as $r => $value)
			{
				if (strpos($value, '_rm_ace@') !== false)
				{
					unset($recipient[$r]);
				}
			}
		}
		elseif (strpos($recipient, '_rm_ace@') !== false)
		{
			$recipient = null;
		}

		if (empty($recipient))
		{
			throw new \Exception('empty recipient');
		}

		$config = Factory::getApplication()->getConfig();

		$mailer = Factory::getMailer();
		$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
		$mailer->setSubject($subject);
		$mailer->isHtml();
		$mailer->Encoding = 'base64';
		$mailer->Timeout  = $timeout;
		$mailer->addRecipient($recipient);
		$mailer->setBody($body);

		return $mailer->Send();
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