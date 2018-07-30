<?php
/**
 * This is a wrapper class to abstract all TransIP API core methods from regular/simpler usage.
 * 
 * (c) 2017, Nuno Tavares <n.tavares@portavita.eu>
 */

require_once('TransIP-API/Transip/VpsService.php');
require_once('TransIP-API/Transip/DomainService.php');

class PortavitaTransIP
{

	/**
	 * The output format
	 *
	 * @var string
	 */
	public $_outputFormat = 'php';

	/**
	 * The output context
	 *
	 * @var string
	 */
	public $_outputContext = 'VPS';

    const OUTPUT_CTX_VPS = 'VPS';
    const OUTPUT_CTX_DOMAIN = 'Domain';

	/**
	 * The output columns, for each result
     * We use this to limit the sometimes excessive output, so for each result of array of objects,
     * you'll need to extend this array with the fields you want to show
	 *
	 * @var string
	 */
	public $_outputColumns = array( 
        PortavitaTransIP::OUTPUT_CTX_VPS => array(
            'Transip_Vps' => array( 'name', 'description', 'operatingSystem', 'status', 'ipAddress' ),
            'Transip_Snapshot' => array( 'name', 'description', 'dateTimeCreate' ),
            ),
        PortavitaTransIP::OUTPUT_CTX_DOMAIN => array(
            'Transip_DnsEntry' => array( 'name', 'expire', 'type', 'content' ),
            'Transip_Nameserver' => array( 'hostname', 'ipv4', 'ipv6' ),
            ),
        );

    /**
     * Just a helper array to help converting string record types into the necessary constants.
     * Kind of silly since the consts resolve to the exact same string, but good practice
     */
    public $_strToRecType = array( 
        'A'     => Transip_DnsEntry::TYPE_A,
        'AAAA'  => Transip_DnsEntry::TYPE_AAAA,
        'CNAME' => Transip_DnsEntry::TYPE_CNAME,
        'MX'    => Transip_DnsEntry::TYPE_MX,
        'NS'    => Transip_DnsEntry::TYPE_NS,
        'TXT'   => Transip_DnsEntry::TYPE_TXT,
        'SRV'   => Transip_DnsEntry::TYPE_SRV,
        );
        

    /* **********************************************************************************************************
     * COMMON HELPER FUNCTIONS
     * **********************************************************************************************************
     */

    /**
     * We provide this wrapper method to avoid touching the TransIP-API Library.
     */
    function loadCredentials() {
        $credentials_loc = "./transip.credentials.php";
        if ( file_exists($credentials_loc) ) {
            include_once($credentials_loc);
            Transip_ApiSettings::$login = $transip_login;
            Transip_ApiSettings::$privateKey = $transip_privateKey;
        }
    }

    function _outputArray(&$var) {
        switch ($this->_outputFormat) {
        case 'php':
            print_r($var);
            break;
        case 'tab':
            $this->__outputArrayTab($var);
            break;
        case 'csv':
            $this->__outputArrayCsv($var);
            break;
        }
    }

    function __outputArrayTab(&$var) {
        if (count($var)<=0) {
            return false;
        }
        $header = $this->_outputColumns[$this->_outputContext][get_class($var[0])];
        for ($i=0; $i<count($header); $i++) {
            print strtoupper($header[$i])."\t";
        }
        print "\n";
        foreach ($var as $row) {
            foreach ($row as $k => $v) {
                if ( in_array($k, $header) ) {
                    print "$v\t";
                }
            }
            print "\n";
        }
    }
    
    function __outputArrayCsv($var) {
        if (count($var)<0) {
            return false;
        }
        $header = $this->_outputColumns[$this->_outputContext][get_class($var[0])];
        for ($i=0; $i<count($header); $i++) {
            $header2[$i] = strtoupper($header[$i]);
        }
        print implode(",", $header2)."\n";
        foreach ($var as $row) {
            $csvrow = array();
            foreach ($row as $k => $v) {
                if ( in_array($k, $header) ) {
                    $csvrow[] = $v;
                }
            }
            print implode(",", $csvrow)."\n";
        }
    }

    function setOutputFormat($fmt) {
        $this->_outputFormat = $fmt;
    }

    function setOutputContext($ctx) {
        $this->_outputContext = $ctx;
    }

    function error($msg) {
        print "ERROR: $msg\n";
    }

    /* **********************************************************************************************************
     * VPS MANAGEMENT
     * **********************************************************************************************************
     */

    function listVps() {
        try {
            // Get a list of all Vps objects
            $vpsList = Transip_VpsService::getVpses();

            $this->setOutputContext(PortavitaTransIP::OUTPUT_CTX_VPS);
            $this->_outputArray($vpsList);
        } catch (SoapFault $f) {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
    }

    function listSnapshots($vmName) {
        try {
            // Get a list of all snapshots for a vps
            $snapshotList = Transip_VpsService::getSnapshotsByVps($vmName);

            $this->setOutputContext(PortavitaTransIP::OUTPUT_CTX_VPS);
            $this->_outputArray($snapshotList);
        } catch (SoapFault $f) {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
    }

    function createSnapshot($vmName, $snapshotDescription) {
        try {
            // Create snapshot for vps
            Transip_VpsService::createSnapshot($vmName, $snapshotDescription);
            return true;
        } catch (SoapFault $f) {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }

    function removeSnapshot($vmName, $snapshotName) {
        try
        {
            // Remove snapshot for vps
            Transip_VpsService::removeSnapshot($vmName, $snapshotName);
            return true;
        }
        catch(SoapFault $f)
        {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }

    function revertSnapshot($vmName, $snapshotName) {
        try
        {
            // Revert snapshot for vps
            Transip_VpsService::revertSnapshot($vmName, $snapshotName);
            return true;
        }
        catch(SoapFault $f)
        {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }

    /* **********************************************************************************************************
     * DOMAIN / DNS MANAGEMENT
     * **********************************************************************************************************
     */

    function listDomains() {
        try {
            // Get a list of all Vps objects
            $domainList = Transip_DomainService::getDomainNames();
            
            if ( count($domainList)<=0 ) {
                echo 'No domains to list.';
                return true;
            }
            echo "DOMAIN\n";
            foreach ($domainList as $domain) {
                echo $domain."\n";
            }
        } catch (SoapFault $f) {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
    }
        
    function _getRecordType($strType) {
        if (!array_key_exists($strType, $this->_strToRecType)) {
            return 'UNKNOWN';
        }
        return $this->_strToRecType[$strType];
    }
    
    /*
     * This method will either modify and existing DNS record, if it exists, 
     * or add it, if it doesn't. This is made by fetching the entire zone, and 
     * editting it.
     * 
     * Filed a FeatureRequest for individual record maintenance support in the API:
     * * https://www.transip.nl/knowledgebase/idee/701-api-support-editing-single-record/
     */
    function modifyDNSRecord($domainName, $recName, $recTtl, $recType, $recTarget) {
        try
        {
            $domain = Transip_DomainService::getInfo($domainName);
            //print_r($domain);
            //echo '---------------------------------------';
            $found = false;
            for ($i=0; $i<count($domain->dnsEntries); $i++) {
                if ( ($domain->dnsEntries[$i]->name == $recName) and ($domain->dnsEntries[$i]->type == $this->_getRecordType($recType)) ) {
                    $found = true;
                    $domain->dnsEntries[$i]->expire = $recTtl;
                    $domain->dnsEntries[$i]->content = $recTarget;
                }
            }
            if (!$found) {
                $domain->dnsEntries[] = new Transip_DnsEntry($recName, $recTtl, $this->_getRecordType($recType), $recTarget);
            }
            //print_r($domain->dnsEntries);
            Transip_DomainService::setDnsEntries($domainName, $domain->dnsEntries);
            return true;
        }
        catch(SoapFault $f)
        {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }

    function removeDNSRecord($domainName, $recName, $recType) {
        try
        {
            $domain = Transip_DomainService::getInfo($domainName);
            $found = -1;

            //print_r($domain);
            //echo '---------------------------------------';
            for ($i=0; $i<count($domain->dnsEntries); $i++) {
                if ( ($domain->dnsEntries[$i]->name == $recName) and ($domain->dnsEntries[$i]->type == $this->_getRecordType($recType)) ) {
                    echo "Updating the following entry:\n";
                    print_r($domain->dnsEntries[$i]);
                    $found = $i;
                }
            }
            if ($found <= 0) {
                echo "DNS Entry: ${recName}.${domainName} of type ${recType} not found!";
                return false;
            }
            array_splice($domain->dnsEntries, $found, 1);
            //print_r($domain->dnsEntries);
            Transip_DomainService::setDnsEntries($domainName, $domain->dnsEntries);
            return true;
        }
        catch(SoapFault $f)
        {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }

    function listDNSRecords($domainName) {
        try
        {
            $domain = Transip_DomainService::getInfo($domainName);
            #print_r($domain->dnsEntries);
            #for ($i=0; $i<count($domain->dnsEntries); $i++) {
            #    print "{$domain->dnsEntries[$i]->name}\t{$domain->dnsEntries[$i]->expire}\t{$domain->dnsEntries[$i]->type}\t{$domain->dnsEntries[$i]->content}\n";
            #}
            $this->setOutputContext(PortavitaTransIP::OUTPUT_CTX_DOMAIN);
            $this->_outputArray($domain->dnsEntries);
            return true;
        }
        catch(SoapFault $f)
        {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }

    function listZoneNameservers($domainName) {
        try
        {
            $domain = Transip_DomainService::getInfo($domainName);
            $this->setOutputContext(PortavitaTransIP::OUTPUT_CTX_DOMAIN);
            $this->_outputArray($domain->nameservers);
            return true;
        }
        catch(SoapFault $f)
        {
            // It is possible that an error occurs when connecting to the TransIP Soap API,
            // those errors will be thrown as a SoapFault exception.
            echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
        }
        return false;
    }
        
}
