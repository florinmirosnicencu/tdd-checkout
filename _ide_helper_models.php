<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Order
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ticket[] $tickets
 */
	class Order extends \Eloquent {}
}

namespace App{
/**
 * App\Ticket
 *
 * @method static \Illuminate\Database\Query\Builder|\App\Ticket available()
 */
	class Ticket extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\Concert
 *
 * @property-read mixed $formatted_date
 * @property-read mixed $formatted_start_time
 * @property-read mixed $ticket_price_in_dollars
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Order[] $orders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ticket[] $tickets
 * @method static \Illuminate\Database\Query\Builder|\App\Concert published()
 */
	class Concert extends \Eloquent {}
}

