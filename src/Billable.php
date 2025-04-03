<?php

namespace Freemius\Laravel;

use Freemius\Laravel\Concerns\ManagesCheckouts;
use Freemius\Laravel\Concerns\ManagesCustomer;
use Freemius\Laravel\Concerns\ManagesLicenses;
use Freemius\Laravel\Concerns\ManagesOrders;
use Freemius\Laravel\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesCheckouts;
    use ManagesCustomer;
    use ManagesLicenses;
    use ManagesOrders;
    use ManagesSubscriptions;
}