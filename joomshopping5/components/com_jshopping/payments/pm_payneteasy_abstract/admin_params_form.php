<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="col100">
    <fieldset class="adminform">
        <table class="admintable" width = "100%" >
            <tr>
                <td  class="key">
                    <?php echo \Joomla\CMS\Language\Text::_('JSHOP_PAYNETEASY_END_POINT')?>
                </td>
                <td>
                    <input
                        type="text"
                        class="inputbox"
                        name="pm_params[end_point]"
                        size="45"
                        value="<?php print $params['end_point'] ?>"
                    />
                    <?php print JHTML::tooltip(\JText::_('JSHOP_PAYNETEASY_END_POINT_DESCRIPTION')); ?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php print \JText::_('JSHOP_PAYNETEASY_LOGIN'); ?>
                </td>
                <td>
                    <input
                        type="text"
                        class="inputbox"
                        name="pm_params[login]"
                        size="45"
                        value="<?php print $params['login'] ?>"
                    />
                    <?php print JHTML::tooltip(\JText::_('JSHOP_PAYNETEASY_LOGIN_DESCRIPTION')); ?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php print \JText::_('JSHOP_PAYNETEASY_SIGNING_KEY'); ?>
                </td>
                <td>
                    <input
                        type="text"
                        class="inputbox"
                        name="pm_params[signing_key]"
                        size="45"
                        value="<?php print $params['signing_key'] ?>"
                    />
                    <?php print JHTML::tooltip(\JText::_('JSHOP_PAYNETEASY_SIGNING_KEY_DESCRIPTION')); ?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php print \JText::_('JSHOP_PAYNETEASY_SANDBOX_GATEWAY'); ?>
                </td>
                <td>
                    <input
                        type="text"
                        class="inputbox"
                        name="pm_params[sandbox_gateway]"
                        size="45"
                        value="<?php print $params['sandbox_gateway'] ?>"
                    />
                    <?php print JHTML::tooltip(JText::_('JSHOP_PAYNETEASY_SANDBOX_GATEWAY_DESCRIPTION')); ?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php print \JText::_('JSHOP_PAYNETEASY_PRODUCTION_GATEWAY'); ?>
                </td>
                <td>
                    <input
                        type="text"
                        class="inputbox"
                        name="pm_params[production_gateway]"
                        size="45"
                        value="<?php print $params['production_gateway'] ?>"
                    />
                    <?php print JHTML::tooltip(\JText::_('JSHOP_PAYNETEASY_PRODUCTION_GATEWAY_DESCRIPTION')); ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php print \JText::_('JSHOP_PAYNETEASY_GATEWAY_MODE'); ?>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.genericlist', $gatewayModeList,
                                   'pm_params[gateway_mode]',
                                   'class = "inputbox" size = "1"', 'value', 'text',
                                   $params['gateway_mode']);
                    print " " . JHTML::tooltip(\JText::_('JSHOP_PAYNETEASY_GATEWAY_MODE_DESCRIPTION'));
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo \JText::_('JSHOP_TRANSACTION_END')?>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.genericlist', $orderStatusList,
                                   'pm_params[transaction_end_status]',
                                   'class = "inputbox" size = "1"', 'status_id', 'name',
                                   $params['transaction_end_status']);
                    print " " . JHTML::tooltip(\JText::_('JSHOP_PAYPAL_TRANSACTION_END_DESCRIPTION'));
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php print \JText::_('JSHOP_TRANSACTION_PENDING'); ?>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.genericlist', $orderStatusList,
                                  'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id',
                                  'name', $params['transaction_pending_status']);
                    print " " . JHTML::tooltip(\JText::_('JSHOP_PAYPAL_TRANSACTION_PENDING_DESCRIPTION'));
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php print \JText::_('JSHOP_TRANSACTION_FAILED'); ?>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.genericlist', $orderStatusList,
                                  'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id',
                                  'name', $params['transaction_failed_status']);
                    print " " . JHTML::tooltip(\JText::_('JSHOP_PAYPAL_TRANSACTION_FAILED_DESCRIPTION'));
                    ?>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="clr"></div>