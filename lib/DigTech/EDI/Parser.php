<?php

namespace DigTech\EDI;

/**
 * \class Parser
 * \ingroup EDI
 * \brief This class provides functionality for processing EDI files
 */

class Parser
{
   const ELEMENT_SEPARATOR = '*';

   protected $_data;                      ///< document data split into lines
   protected $_transactionCount = 0;      ///< count number of ST segments

   public $_interchange  = [];            ///< where is the ISA segment?
   public $_group        = [];            ///< where is the GS segment?
   public $_transactions = [];            ///< where are the ST segment(s)?

   protected $_segmentIdList = [];        ///< used to keep segment indices unique in output

   protected $_ediStructure =             ///< defines EDI structure and child segments
   [
      'ISA'    => ['end' => 'IEA'],
      'GS'     => ['end' => 'GE'],
      'ST'     => ['end' => 'SE'],
      'BEG'    => true,
      'PER'    => true,
      'DTM'    => true,
      'N1'     => ['children' => ['N2','N3','N4']],
      'PO1'    => ['children' => ['PID','CTP']],
      'MSG'    => true,
      'N9'     => true,
      'ACK'    => true,
      'BAK'    => true,
   ];
   protected $_ediToJSON =                ///< map the EDI element name into a human readable name
   [
      'ISA.01' => 'AuthorizationInformationQualifier',
      'ISA.02' => 'AuthorizationInformation',
      'ISA.03' => 'SecurityInformationQualifier',
      'ISA.04' => 'SecurityInformation',
      'ISA.05' => 'InterchangeIDQualifier',
      'ISA.06' => 'InterchangeSenderId',
      'ISA.07' => 'InterchangeIDQualifier2',
      'ISA.08' => 'InterchangeReceiverID',
      'ISA.09' => 'InterchangeDate',
      'ISA.10' => 'InterchangeTime',
      'ISA.11' => 'InterchangeCtrlStdID',
      'ISA.12' => 'InterchangeCtrlVersion',
      'ISA.13' => 'InterchangeCtrlNum',
      'ISA.14' => 'AcknowledgeRequested',
      'ISA.15' => 'UsageIndicator',
      'ISA.16' => 'ComponentElementSeparator',

      'GS.01' => 'FunctionalIdentifierCode',
      'GS.02' => 'ApplicationSenderCode',
      'GS.03' => 'ApplicationReceiverCode',
      'GS.04' => 'GSDate',
      'GS.05' => 'GSTime',
      'GS.06' => 'GroupControlNumber',
      'GS.07' => 'ResponsibleAgencyCode',
      'GS.08' => 'VersionIdentifierCode',

      'ST.01' => 'TransactionSetID',
      'ST.02' => 'TransactionControlNumber',

      'BEG.01' => 'PurposeCode',
      'BEG.02' => 'ControlNumbber',
      'BEG.03' => 'PurchaseOrder',
      'BEG.04' => 'ReleaseNumber',
      'BEG.05' => 'OrderDate',
      'BEG.06' => 'ContractNumber',
      'BEG.07' => 'Unknown',
      'BEG.08' => 'InvoiceType',

      'PER.01' => 'ContactType',
      'PER.02' => 'ContactName',
      'PER.03' => 'CommunicationQualifier',
      'PER.04' => 'CommunicationNumber',

      'N1.01' => 'Type',
      'N1.02' => 'Customer',
      'N1.03' => 'IDCodeQualifier',
      'N1.04' => 'IDCode',
      'N3.01' => 'Street1',
      'N3.02' => 'Street2',
      'N4.01' => 'City',
      'N4.02' => 'State',
      'N4.03' => 'ZipCode',
      'N4.04' => 'Country',
      'N4.05' => 'LocationQualifier',
      'N4.06' => 'LocationIdentifier',

      'PO1.01' => 'LineNumber',
      'PO1.02' => 'QuantityOrdered',
      'PO1.03' => 'UOM',
      'PO1.04' => 'UnitPrice',
      'PO1.05' => 'UnitPriceBasis',
      'PO1.06' => 'IDQualifier1',
      'PO1.07' => 'ProductID1',
      'PO1.08' => 'IDQualifier2',
      'PO1.09' => 'ProductID2',
      'PO1.10' => 'IDQualifier3',
      'PO1.11' => 'ProductID3',
      'PO1.12' => 'IDQualifier4',
      'PO1.13' => 'ProductID4',
      'PO1.14' => 'IDQualifier5',
      'PO1.15' => 'ProductID5',
      'PO1.16' => 'IDQualifier6',
      'PO1.17' => 'ProductID6',
      'PO1.18' => 'IDQualifier7',
      'PO1.19' => 'ProductID7',
      'PO1.20' => 'IDQualifier8',
      'PO1.21' => 'ProductID8',
      'PO1.22' => 'IDQualifier9',
      'PO1.23' => 'ProductID9',
      'PO1.24' => 'IDQualifier10',
      'PO1.25' => 'ProductID10',

      'CTP.01' => 'ClassOfTradeCode',
      'CTP.02' => 'PriceType',
      'CTP.03' => 'Price',

      'PID.01' => 'DescriptionType',
      'PID.02' => 'CharacteristicCode',
      'PID.03' => 'AgencyQualfierCode',
      'PID.04' => 'ProductDescriptionCode',
      'PID.05' => 'Description',

      'DTM.01' => 'DateType',
      'DTM.02' => 'DateValue',

      'CTT.01' => 'ItemCount',
      'CTT.02' => 'HashTotal',
      'CTT.03' => 'Weight',
      'CTT.04' => 'WeightUnit',
      'CTT.05' => 'Volume',
      'CTT.06' => 'VolumeUnit',
      'CTT.07' => 'TotalDescription',

      'REF.01' => 'ReferenceQualifier',
      'REF.02' => 'ReferenceIdentification',
      'REF.03' => 'ReferenceAdditional1',
      'REF.04' => 'ReferenceAdditional2',

      'SE.01' => 'NumberOfSegments',
      'SE.02' => 'ControlNumber',

      'GE.01' => 'TransactionSetCount',
      'GE.02' => 'GroupControlNumber',

      'IEA.01' => 'GroupCount',
      'IEA.02' => 'InterchangeControlNumber',

      'BAK.01' => 'TransactionSetPurpose',
      'BAK.02' => 'AcknowledgementType',
      'BAK.03' => 'BakPurchaseOrderNumber',
      'BAK.04' => 'BakDate',
      'BAK.05' => 'ReleaseNumber',
      'BAK.06' => 'RequestReferenceNumber',
      'BAK.07' => 'ContractNumber',
      'BAK.08' => 'BakReferenceID',
      'BAK.09' => 'BakDate2',

      'CUR.01' => 'EntityIDCode',
      'CUR.02' => 'CurrencyCode',

      'ACK.01' => 'LineItemStatus',
      'ACK.02' => 'AckQuantity',
      'ACK.03' => 'AckUOM',
      'ACK.04' => 'DTQualifier',
      'ACK.05' => 'AckDate',
      'ACK.06' => 'RequestReferenceNumber',
      'ACK.07' => 'ProductIDQualifier',
      'ACK.08' => 'AckProductID',

      'N9.01' => 'ReferenceIDQualifier',
      'N9.02' => 'ReferenceIdentification',
      
      'MSG.01' => 'FreeformMessage',
      'MSG.02' => 'CarriageControlCodes',
   ];
   protected $_jsonToEDI = [];            ///< reverse mapping of human readable name back to EDI element name

   /**
    * \brief Object constructor
    * \param $document The text document to be parsed
    */
   public function __construct($document)
   {
      $this->createEDItoJSONMap();

      // normalize line endings
      $document = str_replace("\r\n", "\n", $document);
      $document = str_replace("\r", "", $document);

      // break out each line
      $this->_data = explode("\n", $document);

      // initialize transaction index (increment on 'SE' element)
      $transaction = 0;

      // get start/end markers
      foreach($this->_data as $index => $line)
      {
         list($element) = explode(self::ELEMENT_SEPARATOR, $line);
//printf("Processing Element: %s on line %d\n", $element, $index);
         switch($element)
         {
            case 'ISA':
               $this->_interchange['start'] = $index;
               break;
            case 'IEA':
               $this->_interchange['end'] = $index;
               break;
            case 'GS':
               $this->_group['start'] = $index;
               break;
            case 'GE':
               $this->_group['end'] = $index;
               break;
            case 'ST':
               $this->_transactions[$transaction]['start'] = $index;
               break;
            case 'SE':
               $this->_transactions[$transaction]['end'] = $index;
               $transaction++;
               break;
         }
      }

      $this->_transactionCount = $transaction;
   }

   /**
    * \brief Create the $_jsonToEDI member variable
    */
   protected function createEDItoJSONMap()
   {
      $this->_jsonToEDI = [];

      foreach($this->_ediToJSON as $edi => $json)
      {
         list($segment) = explode('.', $edi);
         $segment = $this->getParentSegment($segment);
         $this->_jsonToEDI[$segment][$json] = $edi;
      }
   }

   /**
    * \brief Get the number of transactions (ST/SE pairs)
    * \returns Number of transactions contained in EDI document
    */
   public function getTransactionCount()
   {
      return count($this->_transactions);
   }

   /**
    * \brief Get the data lines for a transaction
    * \param $transactionNumber The index of the transaction to retrieve
    * \returns The data lines for the transaction
    */
   public function getTransaction($transactionNumber)
   {
      $ret = [];

      if(isset($this->_transactions[$transactionNumber]))
      {
         for($index = $this->_transactions[$transactionNumber]['start'];
             $index <= $this->_transactions[$transactionNumber]['end'];
             $index++)
         {
            $ret[] = $this->_data[$index];
         }
      }
      return $ret;
   }

   /**
    * \brief Look up the parent segment from a child segment
    * \param $segment The segment to retrieve the parent for (if it is a child)
    * \returns The parent segment if the input segment is a child, the input segment otherwise
    */
   protected function getParentSegment($segment)
   {
      $ret = $segment;

      if(!isset($this->_ediStructure[$segment]))
      {
         foreach($this->_ediStructure as $parent => $properties)
         {
               if(isset($properties['children']) &&
                  is_array($properties['children']) &&
                  in_array($segment, $properties['children']))
               {
                  $ret = $parent;
               }
         }
      }
      return $ret;
   }

   /**
    * \brief Get the segment counter for each segment type
    * \param $segment The segment to obtain the ID (index) for
    * \returns The index for the input segment
    */
   protected function getSegmentID($segment)
   {
      if(!isset($this->_segmentIdList[$segment]))
      {
         $this->_segmentIdList[$segment] = 0;
      }
      $this->_segmentIdList[$segment]++;

      return $segment . '_' . $this->_segmentIdList[$segment];
   }

   /**
    * \brief Retrieve this object as a JSON document
    * \returns JSON document of public properties
    */
   public function toJSON()
   {
      return json_encode($this, JSON_PRETTY_PRINT);
   }

   /**
    * \brief Extract the elements from an EDI segment and get the element name/key
    * \param $elements The array contain the elements
    * \param $useTextProperty If true, use the readable text name or EDI index
    * \returns Array of elements index by text name or EDI index
    */
   protected function extractElements($elements, $useTextProperty=false)
   {
      $ret = [];

      $prefix = $elements[0];

      for($index = 1; $index < count($elements); $index++)
      {
         $subscript = sprintf("%s.%02d", $prefix, $index);
         if($useTextProperty && isset($this->_ediToJSON[$subscript]))
         {
            $subscript = $this->_ediToJSON[$subscript];
         }

         if(isset($subscript))
         {
            $ret[$subscript] = trim($elements[$index]);
         }
      }

      return $ret;
   }

   /**
    * \brief Parse an EDI document
    * \param $document Array of lines contained in EDI document
    * \returns Array of elements index by segment name and element index
    */
   public function parseEDIDocument($document)
   {
      $lineNumber = 0;
      $ediData = [];

      while($lineNumber < count($document))
      {
         // skip blank lines
         if(strlen($document[$lineNumber]) == 0)
         {
            $lineNumber++;
            continue;
         }

         // extract the elements for the line
         $elements = explode("*", $document[$lineNumber]);

         // check if this is a loop element
         $loopName = $elements[0];
         if(isset($this->_ediStructure[$loopName]['children']))
         {
            // initialize the loop daa
            $loopData = [];

            // extract the initial line as any other
            $loopData = $this->extractElements($elements);

            // process the children
            for($index = 0;
               $index < count($this->_ediStructure[$loopName]['children']);
               $index++)
            {
               $lineNumber++;

               // extract the child elements
               $elements = explode("*", $document[$lineNumber]);
               // is this a valid child?
               if(in_array($elements[0], $this->_ediStructure[$loopName]['children']))
               {
                  // merge it into the array
                  $loopData = array_merge($loopData, $this->extractElements($elements));
               }
               else
               {
                  // this isn't a valid child so back up one line
                  $lineNumber--;
                  break;
               }
            }

            // save the loop data
            $ediData[$this->getSegmentID($loopName)] = $loopData;
         }
         else
         {
            // save the element
            $ediData[$this->getSegmentID($elements[0])] = $this->extractElements($elements);
         }

         // go to the next line
         $lineNumber++;
      }

      return $ediData;
   }

   /**
    * \brief Output an EDI document from the input JSON data
    * \param $jsonData Array containing an EDI-formatted JSON document (see parseEDIDocument())
    * \param $property The JSON property name containing the EDI-formatted JSON
    * \param $useTextProperty Use the EDI element index or the readable text name
    */
   public function parseJSONDocument($jsonData, $property, $useTextProperty=false)
   {
      $ret = [];

      $mappingArray = ($useTextProperty === true) ? $this->_ediToJSON : $this->_jsonToEDI;

      foreach($jsonData->$property as $section => $data)
      {
         list($segment) = explode('_', $section);

         if(isset($this->_jsonToEDI[$segment]))
         {
               $lines = [];
               foreach($data as $name => $value)
               {
                  if($useTextProperty)
                  {
                     if(isset($mappingArray[$segment][$name]))
                     {                        
                           $key = $this->_jsonToEDI[$segment][$name];
                     }
                  }
                  else
                  {
                     $key = $name;
                  }

                  list($skey, $index) = explode('.', $key);
                  $lines[$skey][0] = $skey;
                  $lines[$skey][$index] = $value;
               }
      
               foreach($lines as $line)
               {
                  $ret[] = implode('*', $line);
               }
         }
      }

      return $ret;
   }
}

?>
