<?php defined('_JEXEC') or die(); ?>

<script type="text/javascript">
    function check_<?php print $paymentPluginClass; ?>(){
        jQuery('#payment_form').submit();
    }
</script>