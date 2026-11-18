<div>
    <h2><?php print \JText::_('JSHOP_PAYNETEASY_PAYMENT_PROCESSING'); ?></h2>

    <p><?php print \JText::_('JSHOP_PAYNETEASY_PAGE_REFRESH'); ?></p>
    <p><?php print \JText::_('JSHOP_PAYNETEASY_NEXT_REFRESH'); ?> <span id="seconds-remaining">5</span> <?php print \JText::_('JSHOP_PAYNETEASY_SECONDS'); ?></p>

    <form action="<?php print $formAction; ?>" name="update_status" method="post">
        <input type="submit" value="Check result" />
    </form>
</div>
<script type="text/javascript">
    (function(document)
    {
        function countdown()
        {
            if (document.getElementById("seconds-remaining").innerHTML > 0)
            {
                --document.getElementById("seconds-remaining").innerHTML;
                setTimeout(countdown, 1000);
            }
            else
            {
                document.update_status.submit();
            }
        }

        countdown();
    })(document);
</script>