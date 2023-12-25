<?php
/*
 * @package     Abandoned Cart Plugin
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2023 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  object $cart     Cart object.
 * @var  object $customer Customer object.
 *
 */

$root = Uri::getInstance()->toString(['scheme', 'host', 'port']);

$link = $root . $cart->link;
?>
<div>
	<h1>
		<a href="<?php echo $link; ?>">
			<?php echo Text::sprintf("PLG_RADICALMART_MESSAGE_EMAIL_CART_HEADER"); ?>
		</a>
	</h1>
	<table style="width: 100%; border: 1px solid #ddd; border-collapse: collapse;border-spacing: 0;">
		<thead>
		<tr>
			<th style="text-align: left; vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; ">
				<?php echo Text::_('COM_RADICALMART_PRODUCT'); ?>
			</th>
			<th style="vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; text-align: right;">
				<?php echo Text::_('COM_RADICALMART_PRICE'); ?>
			</th>
			<th style="vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; text-align: center;">
				<?php echo Text::_('COM_RADICALMART_QUANTITY'); ?>
			</th>
			<th style=" vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; text-align: right;">
				<?php echo Text::_('COM_RADICALMART_SUM'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$i = 0;
		foreach ($cart->products as $p => $product) :
			$style = 'padding: 8px; line-height: 18px; text-align: left; vertical-align: top;border-top: 1px solid #ddd;';
			if ($i % 2)
			{
				$style .= 'background-color: #f9f9f9;';
			}
			$i++;
			?>
			<tr>
				<td style="<?php echo $style; ?>">
					<div>
						<?php if ($product->link) : ?>
							<a href="<?php echo $root . $product->link; ?>" style="word-wrap:break-word;">
								<?php echo $product->title; ?>
							</a>
						<?php else: ?>
							<?php echo $product->title; ?>
						<?php endif; ?>
					</div>
					<?php if (!empty($product->extra_display)): ?>
						<div>
							<?php foreach ($product->extra_display as $extra):
								if (empty($extra) || (empty($extra['html']) && empty($extra['notification_html'])))
								{
									continue;
								}
								?>
								<div>
									<?php echo (!empty($extra['notification_html'])) ? $extra['notification_html'] :
										$extra['html']; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</td>
				<td style="<?php echo $style; ?> text-align: right;border-left: 1px solid #ddd;">
					<?php if ($product->order['discount_enable']): ?>
						<div style="font-size: 12px; color: #ccc">
							<s><?php echo $product->order['base_seo']; ?></s>
							<?php echo ' ( - ' . $product->order['discount_seo'] . ')'; ?>
						</div>
					<?php endif; ?>
					<div>
						<?php echo str_replace(' ', '&nbsp;', $product->order['final_seo']); ?>
					</div>
				</td>
				<td style="<?php echo $style; ?> text-align: center;border-left: 1px solid #ddd;">
					<?php echo $product->order['quantity']; ?>
				</td>
				<td style="<?php echo $style; ?> text-align: right;border-left: 1px solid #ddd;">
					<?php if ($product->order['discount_enable']): ?>
						<div style="font-size: 12px; color: #ccc">
							<s><?php echo $product->order['sum_base_seo']; ?></s>
							<?php echo ' ( - ' . $product->order['sum_discount_seo'] . ')'; ?>
						</div>
					<?php endif; ?>
					<div>
						<strong>
							<?php echo str_replace(' ', '&nbsp;', $product->order['sum_final_seo']); ?>
						</strong>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="3" style="border-top: 1px solid #ddd;"></td>
			<td style="border-top: 1px solid #ddd; text-align: right;">
				<div style="margin-bottom: 5px;">
					<span><?php echo Text::_('COM_RADICALMART_SUBTOTAL'); ?>: </span>
					<span>
						<?php echo str_replace(' ', '&nbsp;', $cart->total['base_seo']); ?>
					</span>
				</div>
				<?php if (!empty($cart->total['discount'])): ?>
					<div style="margin-bottom: 5px;">
						<span><?php echo Text::_('COM_RADICALMART_PRICE_DISCOUNT'); ?>: </span>
						<span>
							<?php echo str_replace(' ', '&nbsp;', $cart->total['discount_seo']); ?>
						</span>
					</div>
				<?php endif; ?>
				<div style="font-size: 18px; padding: 20px">
					<span><?php echo Text::_('COM_RADICALMART_TOTAL'); ?>: </span>
					<strong>
						<?php echo str_replace(' ', '&nbsp;', $cart->total['final_seo']); ?>
					</strong>
				</div>
			</td>
		</tr>
		</tfoot>
	</table>
</div>