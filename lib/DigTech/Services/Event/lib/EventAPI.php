<?php

namespace DigTech\Services\EventManager;

/**
 * \class EventEnvelope
 * \ingroup Services
 * \brief The envelope provides a wrapper around all subsequent event types
 * \note The class and details are passed as references to objects. This can be useful when iterating
 * through creation of multiple events
 */
class EventEnvelope
{
    public $source;         ///< The source of the event
    public $timestamp;      ///< The event timestamp
    public $performer;      ///< The system that performed the event
    public $class;          ///< The event class name

    /**
     * \brief Object constructor
     * \param $source The source of the event
     * \param $timestamp The event timestamp
     * \param $performer The system that performed the event
     * \param $class The name of the event class
     */
    public function __construct($source, $timestamp, $performer, $class)
    {
        $this->source    = $source;
        $this->timestamp = $timestamp;
        $this->performer = $performer;
        $this->class     = $class;
    }

    /**
     * \brief Set the event class information
     * \param $classObj Reference to class object
     */
    public function setClass(&$classObj)
    {
        $class = $this->class;
        $this->$class = $classObj;
    }

    /**
     * \brief Set the event details
     * \param $detailObj The detail object for the event
     */
    public function setDetail(&$detailObj)
    {
        $this->details = $detailObj;
    }

    /**
     * \brief Get the object as JSON
     * \returns JSON string
     */
    public function toJSON()
    {
        return json_encode(['event' => $this], JSON_PRETTY_PRINT);
    }
}

/**
 * \class EventClassOrder
 * \ingroup Services
 * \brief This object describes order class events
 */
class EventClassOrder
{
    public $salesorder;     ///< Sales order number
    public $customernumber; ///< Customer number
    public $saledate;       ///< Sale date
    public $status;         ///< Order status

    /**
     * \brief Object constructor
     * \param $salesorder The sales order number
     * \param $customernumber The customer associated with the order
     * \param $saledate The date the sales order was created
     * \param $status The status of the sales order
     */
    public function __construct($salesorder, $customernumber, $saledate, $status)
    {
        $this->salesorder = $salesorder;
        $this->customernumber = $customernumber;
        $this->saledate = $saledate;
        $this->status = $status;
    }
}

/**
 * \class EventOrderDetails
 * \ingroup Services
 * \brief This object contains the details for an event on an order class object
 */
class EventOrderDetails
{
    public $evemt;      ///< Event type
    public $source;     ///< The application/source method (API/File)
    public $status;     ///< The status of the event
    public $timestamp;  ///< Timestamp for event

    /**
     * \brief Object constructor
     * \param $event The event that was taken
     * \param $source The source of the event
     * \param $status The status of the event
     * \param $timestamp The time when the event was performed
     */
    public function __construct($event, $source, $status, $timestamp)
    {
        $this->event = $event;
        $this->source = $source;
        $this->status = $status;
        $this->timestamp = $timestamp;
    }
}


/* Unit Tests */

// create and initialize the envelope, event class, and details objects
$env = new EventEnvelope('System', '2023-09-11 23:00', 'Import', 'order');
$class = new EventClassOrder('SO1000-00001', 'CN100-001', '2023-09-11', 'PENDING');
$detail = new EventOrderDetails('OrderReceived', 'API', 'SUCCESS', '2023-09-11 23:00:00');

// put the class and details in the envelope
$env->setClass($class);
$env->setDetail($detail);

// verify output of JSON conversion
printf("%s\n", $env->toJSON());

// change the sales order in the event class object
$class->salesorder = 'SO1000-00002';

// validate that the class was passed by reference in the JSON
printf("%s\n", $env->toJSON());

/* End Unit Tests */

?>