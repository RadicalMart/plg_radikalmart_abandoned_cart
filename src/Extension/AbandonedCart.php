<?php
/*
 * @package     Abandoned Cart Plugin
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2023 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

namespace Joomla\Plugin\RadicalMart\AbandonedCart\Extension;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\AbandonedCart\Console\SendingLetterCommand;
use Joomla\Registry\Registry;

class AbandonedCart extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    bool
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the application object.
	 *
	 * @var  \Joomla\CMS\Application\CMSApplication
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app = null;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onRadicalMartPrepareConfigForm'   => 'onRadicalMartPrepareConfigForm',
			'onRadicalMartRegisterCLICommands' => 'onRadicalMartRegisterCLICommands',
			'onRadicalMartPrepareForm'         => 'onRadicalMartPrepareForm',
			'onRadicalMartBeforeOrderSave'     => 'onRadicalMartBeforeOrderSave'
		];
	}

	/**
	 * Add email config form to RadicalMart.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.1.0
	 */
	public function onRadicalMartPrepareConfigForm(Form $form, $data = [])
	{
		$form->loadFile(JPATH_PLUGINS . '/radicalmart/abandoned_cart/forms/radicalmart.xml');
	}

	/**
	 * Listener for `onRadicalMartRegisterCLICommands` event.
	 *
	 * @param   array     $commands  Updated commands array.
	 * @param   Registry  $params    RadicalMart params.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onRadicalMartRegisterCLICommands(array &$commands, Registry $params)
	{
		$commands[] = SendingLetterCommand::class;
	}

	/**
	 * Method to add fields to form.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws \Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onRadicalMartPrepareForm(Form $form, $data = [])
	{
		$formName = $form->getName();

		if ($formName === 'com_radicalmart.cart')
		{
			$form->loadFile(JPATH_PLUGINS . '/radicalmart/abandoned_cart/forms/com_radicalmart.cart.xml');
		}
		elseif ($formName === 'com_radicalmart.order')
		{
			$form->loadFile(JPATH_PLUGINS . '/radicalmart/abandoned_cart/forms/com_radicalmart.order.xml');
		}
	}

	public function onRadicalMartBeforeOrderSave($context, &$data, $formData, $products, $shipping, $payment, $currency, $isNew)
	{
		if ($isNew && !empty($formData['plugins']['abandoned_cart']))
		{
			if (!isset($data['plugins']))
			{
				$data['plugins'] = '';
			}
			$data['plugins'] = new Registry($data['plugins']);
			$data['plugins']->set('abandoned_cart', $formData['plugins']['abandoned_cart']);
			$data['plugins'] = $data['plugins']->toString();
		}
	}
}