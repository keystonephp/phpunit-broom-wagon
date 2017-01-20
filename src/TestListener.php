<?php

namespace Keystone\PHPUnit\BroomWagon;

use PHPUnit_Framework_BaseTestListener;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_TestSuite;

class TestListener extends PHPUnit_Framework_BaseTestListener
{
    /**
     * @var int
     */
    private $reportLength;

    /**
     * @var int
     */
    private $slowThreshold;

    /**
     * A collection of tests deemed as slow.
     *
     * @var array
     */
    private $slowTests = [];

    /**
     * Internal tracking for test suites.
     *
     * Increments as more suites are run, then decremented as they finish. All
     * suites have been run when returns to 0.
     *
     * @var int
     */
    private $suites = 0;

    public function __construct(array $options)
    {
        $this->reportLength = isset($options['reportLength']) ? $options['reportLength'] : 10;
        $this->slowThreshold = isset($options['slowThreshold']) ? $options['slowThreshold'] : 500;
    }

    public function endTest(PHPUnit_Framework_Test $test, $timeSeconds)
    {
        if (!$test instanceof PHPUnit_Framework_TestCase) {
            return;
        }

        $timeMilliseconds = (int) round($timeSeconds * 1000);
        if ($timeMilliseconds >= $this->getSlowThreshold($test)) {
            $this->slowTests[$test->toString()] = $timeMilliseconds;
        }
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        ++$this->suites;
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        --$this->suites;

        // When reaching the last suite output the report
        if ($this->suites === 0 && count($this->slowTests) > 0) {
            // Display slowest tests first
            arsort($this->slowTests);

            $reportLength = $this->getReportLength();
            $this->printHeader($reportLength);
            $this->printReport($reportLength);
            $this->printFooter($reportLength);
        }
    }

    private function printHeader($reportLength)
    {
        printf(
            "\n\n%s %d slow tests (>%sms):\n",
            count($this->slowTests) > $reportLength ? 'Top' : 'Found',
            $reportLength,
            $this->slowThreshold
        );
    }

    private function printReport($reportLength)
    {
        $index = 0;
        foreach (array_slice($this->slowTests, 0, $reportLength, true) as $label => $time) {
            printf(" %d. %dms to run %s\n", ++$index, $time, $label);
        }
    }

    private function printFooter($reportLength)
    {
        $hiddenCount = count($this->slowTests) - $reportLength;
        if ($hiddenCount > 0) {
            printf("\nThere are %d more tests slower than the threshold.", $hiddenCount);
        }
    }

    private function getReportLength()
    {
        return min(count($this->slowTests), $this->reportLength);
    }

    /**
     * Get the slow threshold for the given test. A test case can override the
     * suite slow threshold by using the annotation @slowThreshold with the
     * threshold value in milliseconds.
     */
    private function getSlowThreshold(PHPUnit_Framework_TestCase $test)
    {
        $annotations = $test->getAnnotations();
        if (isset($annotations['method']['slowThreshold'][0])) {
            return $annotations['method']['slowThreshold'][0];
        }

        return $this->slowThreshold;
    }
}
