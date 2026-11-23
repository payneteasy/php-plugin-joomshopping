<?php defined('_JEXEC') or die(); ?>

<?php print _JSHOP_PAYNETEASY_INSERT_CREDIT_CARD_DATA; ?>
<table>
    <tr>
        <td width="200">
            <?php print _JSHOP_PAYNETEASY_CREDIT_CARD_OWNER; ?>
        </td>
        <td>
            <input
                type="text"
                class="inputbox"
                name="params[pm_payneteasy_sale][credit_card_owner]"
                id="params_pm_payneteasy_sale_credit_card_owner"
                value="<?php print htmlspecialchars(isset($params['credit_card_owner']) ? (string) $params['credit_card_owner'] : '', ENT_QUOTES, 'UTF-8') ?>"
            />
        </td>
    </tr>
    <tr>
        <td>
            <?php print _JSHOP_PAYNETEASY_CREDIT_CARD_NUMBER; ?>
        </td>
        <td>
            <input
                type="text"
                class="inputbox"
                name="params[pm_payneteasy_sale][credit_card_number]"
                id="params_pm_payneteasy_sale_credit_card_number"
                value="<?php print htmlspecialchars(isset($params['credit_card_number']) ? (string) $params['credit_card_number'] : '', ENT_QUOTES, 'UTF-8') ?>"
            />
        </td>
    </tr>
    <tr>
        <td>
            <?php print _JSHOP_PAYNETEASY_CREDIT_CARD_EXPIRE_MONTH; ?>
        </td>
        <td>
            <?php print  JHTML::_(
                'select.integerlist', 1, 12, 1,
                'params[pm_payneteasy_sale][credit_card_expire_month]',
                'class="inputbox" id="params_pm_payneteasy_sale_credit_card_expire_month"',
                $params['credit_card_expire_month']
            ); ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php print _JSHOP_PAYNETEASY_CREDIT_CARD_EXPIRE_YEAR; ?>
        </td>
        <td>
            <?php print  JHTML::_(
                'select.integerlist', $startYear, $endYear, 1,
                'params[pm_payneteasy_sale][credit_card_expire_year]',
                'class="inputbox" id="params_pm_payneteasy_sale_credit_card_expire_year"',
                $params['credit_card_expire_year']
            ); ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php print _JSHOP_PAYNETEASY_CREDIT_CARD_CVV2; ?>
        </td>
        <td>
            <input
                type="text"
                class="inputbox"
                name="params[pm_payneteasy_sale][credit_card_cvv2]"
                id="params_pm_payneteasy_sale_credit_card_cvv2"
                value="<?php print htmlspecialchars(isset($params['credit_card_cvv2']) ? (string) $params['credit_card_cvv2'] : '', ENT_QUOTES, 'UTF-8') ?>"
            />
        </td>
    </tr>
</table>
<script type="text/javascript">
    function check_pm_payneteasy_sale()
    {
        var i,
            errors = [],
            assertNotEmpty = function(id)
        {
            var fullId = 'params_pm_payneteasy_sale_credit_card_' + id;

            if (isEmpty($F_(fullId)))
            {
                errors.push(fullId);
            }
        };

        unhighlightField('payment_form');

        assertNotEmpty('owner');
        assertNotEmpty('number');
        assertNotEmpty('cvv2');

        if (errors.length === 0)
        {
            jQuery('#payment_form').submit();
            return;
        }

        for (i = 0; i < errors.length; i++)
        {
            highlightField(errors[i]);
        }
    }
</script>