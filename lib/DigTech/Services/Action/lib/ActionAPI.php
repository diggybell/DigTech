<?php

namespace DigTech\Services\ActionManager;

/**
 * \class ActionEnvelope
 * \ingroup Services
 * \brief The envelope provides a wrapper around all subsequent action types
 * \note The class and details are passed as references to objects. This can be useful when iterating
 * through creation of multiple events
 */
class ActionEnvelope
{
    public $source;         ///< The source of the action
    public $timestamp;      ///< The action timestamp
    public $performer;      ///< The system that performed the action
    public $class;          ///< The action class name

    /**
     * \brief Object constructor
     * \param $source The source of the action
     * \param $timestamp The action timestamp
     * \param $performer The system that performed the action
     * \param $class The name of the action class
     */
    public function __construct($source, $timestamp, $performer, $class)
    {
        $this->source    = $source;
        $this->timestamp = $timestamp;
        $this->performer = $performer;
        $this->class     = $class;
    }

    /**
     * \brief Set the action class information
     * \param $classObj Reference to class object
     */
    public function setClass(&$classObj)
    {
        $class = $this->class;
        $this->$class = $classObj;
    }

    /**
     * \brief Set the action details
     * \param $detailObj The detail object for the action
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
        return json_encode(['action' => $this], JSON_PRETTY_PRINT);
    }
}

/**
 * \class ActionClassOrder
 * \ingroup Services
 * \brief This object describes order class actions
 */
class ActionClassOrder
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
 * \class ActionOrderDetails
 * \ingroup Services
 * \brief This object contains the details for an action on an order class object
 */
class ActionOrderDetails
{
    public $action;     ///< Action type
    public $source;     ///< The application/source method (API/File)
    public $status;     ///< The status of the action
    public $timestamp;  ///< Timestamp for action

    /**
     * \brief Object constructor
     * \param $action The action that was taken
     * \param $source The source of the action
     * \param $status The status of the action
     * \param $timestamp The time when the action was performed
     */
    public function __construct($action, $source, $status, $timestamp)
    {
        $this->action = $action;
        $this->source = $source;
        $this->status = $status;
        $this->timestamp = $timestamp;
    }
}


/* Unit Tests */

// create and initialize the envelope, action class, and details objects
$env = new ActionEnvelope('System', '2023-09-11 23:00', 'Import', 'order');
$class = new ActionClassOrder('SO1000-00001', 'CN100-001', '2023-09-11', 'PENDING');
$detail = new ActionOrderDetails('OrderReceived', 'API', 'SUCCESS', '2023-09-11 23:00:00');

// put the class and details in the envelope
$env->setClass($class);
$env->setDetail($detail);

// verify output of JSON conversion
printf("%s\n", $env->toJSON());

// change the sales order in the action class object
$class->salesorder = 'SO1000-00002';

// validate that the class was passed by reference in the JSON
printf("%s\n", $env->toJSON());

/* End Unit Tests */

?>