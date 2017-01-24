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
    private $slowThreshold;

    /**
     * @var array
     */
    private $groupSlowThresholds;

    /**
     * @var int
     */
    private $reportLength;

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

    public function __construct($slowThreshold = 500, array $groupSlowThresholds = [], $reportLength = 10)
    {
        $this->slowThreshold = $slowThreshold;
        $this->groupSlowThresholds = $groupSlowThresholds;
        $this->reportLength = $reportLength;
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$test instanceof PHPUnit_Framework_TestCase) {
            return;
        }

        // Convert time from seconds to milliseconds
        $time = (int) round($time * 1000);
        $slowThreshold = $this->getSlowThreshold($test);

        if ($time >= $slowThreshold) {
            $this->slowTests[] = [
                'label' => $this->makeLabel($test),
                'threshold' => $slowThreshold,
                'time' => $time,
            ];
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
            "\n\n%s %d slow tests:\n",
            count($this->slowTests) > $reportLength ? 'Top' : 'Recorded',
            $reportLength
        );
    }

    private function printReport($reportLength)
    {
        $index = 0;
        foreach (array_slice($this->slowTests, 0, $reportLength, true) as $slowTest) {
            printf(
                " %d. %dms to run %s (expected <%dms)\n",
                ++$index,
                $slowTest['time'],
                $slowTest['label'],
                $slowTest['threshold']
            );
        }
    }

    private function printFooter($reportLength)
    {
        $hiddenCount = count($this->slowTests) - $reportLength;
        if ($hiddenCount > 0) {
            printf("\nThere are %d more tests slower than the threshold.", $hiddenCount);
        }
    }

    private function makeLabel(PHPUnit_Framework_TestCase $test)
    {
        return sprintf('%s:%s', get_class($test), $test->getName());
    }

    private function getReportLength()
    {
        return min(count($this->slowTests), $this->reportLength);
    }

    /**
     * Get the slow threshold for the given test. A test case can override the
     * suite or group slow threshold by using the annotation @slowThreshold with the
     * threshold value in milliseconds.
     */
    private function getSlowThreshold(PHPUnit_Framework_TestCase $test)
    {
        $annotations = $test->getAnnotations();
        if (isset($annotations['method']['slowThreshold'][0])) {
            return $annotations['method']['slowThreshold'][0];
        }

        if (isset($annotations['class']['slowThreshold'][0])) {
            return $annotations['class']['slowThreshold'][0];
        }

        // Get the lowest slow threshold for the matching groups
        $matchedSlowThresholds = array_intersect_key(
            $this->groupSlowThresholds,
            array_flip($test->getGroups())
        );

        if (!empty($matchedSlowThresholds)) {
            return min($matchedSlowThresholds);
        }

        // No matching thresholds, use the default
        return $this->slowThreshold;
    }
}
