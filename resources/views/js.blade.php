<?php

$seller = config('freemius.public_key');

?>
<script src="https://checkout.freemius.com/js/v1/"></script>
<script>
    const handler = new FS.Checkout({
        product_id: '<productID>',
        plan_id: '<planID>',
        public_key: '@json($public_key)',
        image: 'https://your-plugin-site.com/logo-100x100.png',
    });
</script>