<?php

namespace Tests\Feature;

use App\Models\StudentResult;
use Tests\TestCase;

class CategoryGradingTest extends TestCase
{
    /**
     * Test max marks for Rainbow categories is 40.
     */
    public function test_rainbow_categories_max_marks_is_40(): void
    {
        $this->assertEquals(40, StudentResult::getDefaultMaxMarks('RAINBOW 3'));
        $this->assertEquals(40, StudentResult::getDefaultMaxMarks('RAINBOW 4'));
        $this->assertEquals(40, StudentResult::getDefaultMaxMarks('RAINBOW 5'));
        $this->assertEquals(40, StudentResult::getDefaultMaxMarks('Rainbow'));
    }

    /**
     * Test max marks for Planets category is 50.
     */
    public function test_planets_category_max_marks_is_50(): void
    {
        $this->assertEquals(50, StudentResult::getDefaultMaxMarks('PLANET'));
        $this->assertEquals(50, StudentResult::getDefaultMaxMarks('PLANETS'));
    }

    /**
     * Test max marks for Galaxy categories is 60.
     */
    public function test_galaxy_categories_max_marks_is_60(): void
    {
        $this->assertEquals(60, StudentResult::getDefaultMaxMarks('GALAXY HS'));
        $this->assertEquals(60, StudentResult::getDefaultMaxMarks('GALAXY HSS (ARTS)'));
        $this->assertEquals(60, StudentResult::getDefaultMaxMarks('GALAXY HSS (SCIENCE)'));
    }

    /**
     * Test Rainbow grading thresholds:
     * 90% and Above = A+
     * 80% and Above = A
     * 70% and Above = B+
     * 60% and Above = B
     */
    public function test_rainbow_grading_thresholds(): void
    {
        $this->assertEquals('A+', StudentResult::calculateGrade(95.0, 'RAINBOW 3'));
        $this->assertEquals('A+', StudentResult::calculateGrade(90.0, 'RAINBOW 4'));
        $this->assertEquals('A', StudentResult::calculateGrade(89.9, 'RAINBOW 5'));
        $this->assertEquals('A', StudentResult::calculateGrade(80.0, 'RAINBOW 3'));
        $this->assertEquals('B+', StudentResult::calculateGrade(75.0, 'RAINBOW 4'));
        $this->assertEquals('B+', StudentResult::calculateGrade(70.0, 'RAINBOW 5'));
        $this->assertEquals('B', StudentResult::calculateGrade(65.0, 'RAINBOW 3'));
        $this->assertEquals('B', StudentResult::calculateGrade(60.0, 'RAINBOW 4'));
        $this->assertNull(StudentResult::calculateGrade(59.9, 'RAINBOW 5'));
    }

    /**
     * Test Planets grading thresholds:
     * 90% and Above = A+
     * 80% and Above = A
     * 70% and Above = B+
     * 60% and Above = B
     */
    public function test_planets_grading_thresholds(): void
    {
        $this->assertEquals('A+', StudentResult::calculateGrade(92.0, 'PLANET'));
        $this->assertEquals('A+', StudentResult::calculateGrade(90.0, 'PLANETS'));
        $this->assertEquals('A', StudentResult::calculateGrade(85.0, 'PLANET'));
        $this->assertEquals('A', StudentResult::calculateGrade(80.0, 'PLANETS'));
        $this->assertEquals('B+', StudentResult::calculateGrade(75.0, 'PLANET'));
        $this->assertEquals('B+', StudentResult::calculateGrade(70.0, 'PLANETS'));
        $this->assertEquals('B', StudentResult::calculateGrade(62.0, 'PLANET'));
        $this->assertEquals('B', StudentResult::calculateGrade(60.0, 'PLANETS'));
        $this->assertNull(StudentResult::calculateGrade(55.0, 'PLANET'));
    }

    /**
     * Test Galaxy grading thresholds:
     * 85% and Above = A+
     * 70% and Above = A
     * 55% and Above = B+
     * 40% and Above = B
     */
    public function test_galaxy_grading_thresholds(): void
    {
        $this->assertEquals('A+', StudentResult::calculateGrade(88.0, 'GALAXY HS'));
        $this->assertEquals('A+', StudentResult::calculateGrade(85.0, 'GALAXY HSS (ARTS)'));
        $this->assertEquals('A', StudentResult::calculateGrade(84.9, 'GALAXY HSS (SCIENCE)'));
        $this->assertEquals('A', StudentResult::calculateGrade(70.0, 'GALAXY HS'));
        $this->assertEquals('B+', StudentResult::calculateGrade(68.0, 'GALAXY HSS (ARTS)'));
        $this->assertEquals('B+', StudentResult::calculateGrade(55.0, 'GALAXY HSS (SCIENCE)'));
        $this->assertEquals('B', StudentResult::calculateGrade(54.9, 'GALAXY HS'));
        $this->assertEquals('B', StudentResult::calculateGrade(40.0, 'GALAXY HSS (ARTS)'));
        $this->assertNull(StudentResult::calculateGrade(39.9, 'GALAXY HSS (SCIENCE)'));
    }
}
