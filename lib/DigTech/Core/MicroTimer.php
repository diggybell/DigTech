<?php

namespace DigTech\Core;

/**
 * \class MicroTimer
 * \ingroup Core
 * \brief This class implements a timer based on microseconds.
 */


class MicroTimer extends ElapsedTimer
{
   public function start()
   {
      $this->_start = microtime(true);
   }

   public function end()
   {
      $this->_end = microtime(true);
   }

   public function interval()
   {
      return microtime(true) - $this->_start;
   }
}
