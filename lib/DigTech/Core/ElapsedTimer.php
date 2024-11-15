<?php

namespace DigTech\Core;

/**
 * \class ElapsedTimer
 * \ingroup Core
 * \brief This class implements a timer based on seconds.
 */

 class ElapsedTimer
{
   protected $_start;
   protected $_end;

   public function __construct()
   {
      $this->reset();
   }

   public function reset()
   {
      $this->_start = 0;
      $this->_end = 0;
   }

   public function start()
   {
      if($this->_start == 0)
      {
         $this->_start = time();
      }
   }

   public function end()
   {
      if($this->_end == 0)
      {
         $this->_end = time();
      }
   }

   public function interval()
   {
      return time() - $this->_start;
   }

   public function elapsed()
   {
      if($this->_start > $this->_end)
      {
         return -1;
      }
      return $this->_end - $this->_start;
   }
}
