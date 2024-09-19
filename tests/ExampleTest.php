<?php

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use QueueWatch\QueueWatch\Commands\QueueWatchCommand;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

beforeEach(function () {
    $this->commandForTest = new QueueWatchCommand;
    $this->command = Mockery::mock(QueueWatchCommand::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    // $this->command->setLaravel(app());
    $this->command->shouldReceive('argument')->andReturn(null);
    $this->command->shouldReceive('option')->andReturn(null);
});

test('command signature is correct', function () {
    expect($this->commandForTest->getName())->toBe('queue:work:watch');
});

test('command description is set', function () {
    expect($this->commandForTest->getDescription())->not()->toBeEmpty();
});

test('getWatchDirectories returns correct directories', function () {
    $directories = $this->command->getWatchDirectories();

    expect($directories)->toBeArray();
    foreach ($directories as $dir) {
        expect($dir)->toBeDirectory();
    }
});

test('getQueueWorkArguments returns correct arguments', function () {
    $args = $this->command->getQueueWorkArguments();

    expect($args)->toBeArray();
    expect($args)->toContain('--verbose');
});

test('startQueueWorker creates a process', function () {
    // Mock the command
    $this->command->setLaravel(app());
    $input = new StringInput('');
    $output = new BufferedOutput;

    $outputStyle = new OutputStyle($input, $output);

    $this->command->setOutput($outputStyle);

    $this->command->shouldReceive('info')->with('Queue worker started.');

    // Call the method being tested
    $this->command->startQueueWorker();

    // Check if a process was created
    $processProperty = new ReflectionProperty($this->command, 'process');
    $processProperty->setAccessible(true);
    $process = $processProperty->getValue($this->command);

    // Assert that the process is an instance of Symfony's Process class
    expect($process)->toBeInstanceOf(Process::class);
});

test('stopQueueWorker stops the running process', function () {
    $mockProcess = Mockery::mock(Process::class);
    $mockProcess->shouldReceive('isRunning')->once()->andReturn(true);
    $mockProcess->shouldReceive('stop')->once();

    $processProperty = new ReflectionProperty($this->command, 'process');
    $processProperty->setAccessible(true);
    $processProperty->setValue($this->command, $mockProcess);

    // $this->command->expectsOutput('Queue worker stopped.');

    $this->command->setLaravel(app());
    $input = new StringInput('');
    $output = new BufferedOutput;

    $outputStyle = new OutputStyle($input, $output);

    $this->command->setOutput($outputStyle);
    $this->command->shouldReceive('info')->once()->with('Queue worker stopped.');
    $this->command->stopQueueWorker();
});

test('getLastModifiedTime returns correct timestamp', function () {
    $finder = Mockery::mock(Finder::class);
    $file1 = Mockery::mock();
    $file1->shouldReceive('getMTime')->andReturn(1000);
    $file2 = Mockery::mock();
    $file2->shouldReceive('getMTime')->andReturn(2000);

    $finder->shouldReceive('getIterator')->andReturn(new ArrayIterator([$file1, $file2]));

    $lastModified = $this->command->getLastModifiedTime($finder);

    expect($lastModified)->toBe(2000);
});

test('monitorQueueWorker restarts stopped process', function () {
    $mockProcess = Mockery::mock(Process::class);
    $mockProcess->shouldReceive('isRunning')->once()->andReturn(false);

    $processProperty = new ReflectionProperty($this->command, 'process');
    $processProperty->setAccessible(true);
    $processProperty->setValue($this->command, $mockProcess);
    $this->command->setLaravel(app());
    $input = new StringInput('');
    $output = new BufferedOutput;

    $outputStyle = new OutputStyle($input, $output);

    $this->command->setOutput($outputStyle);
    $this->command->shouldReceive('error')->once()->with('Queue worker stopped unexpectedly. Restarting...');
    $this->command->shouldReceive('startQueueWorker')->once();

    $this->command->monitorQueueWorker();
});

test('handle method runs correctly when no directories to watch', function () {
    $this->command->shouldReceive('startQueueWorker')->once();
    $this->command->shouldReceive('monitorQueueWorker')->once();
    $this->command->shouldReceive('getWatchDirectories')->andReturn([]);
    $this->command->setLaravel(app());
    $input = new StringInput('');
    $output = new BufferedOutput;

    $outputStyle = new OutputStyle($input, $output);

    $this->command->setOutput($outputStyle);
    $this->command->shouldReceive('error')->once()->with('No directories to watch. The queue worker will run without file watching.');

    $this->command->handle();
});

// test('handle method detects file changes and restarts worker', function () {
//     // Set up the Laravel app for the command
//     $this->command->setLaravel(app());

//     // Set up input and output streams for OutputStyle
//     $input = new StringInput('');
//     $output = new BufferedOutput();
//     $outputStyle = new OutputStyle($input, $output);

//     // Assign the OutputStyle to the command
//     $this->command->setOutput($outputStyle);

//     // Mock the command methods

//     $this->command->shouldReceive('startQueueWorker');
//     $this->command->shouldReceive('stopQueueWorker');
//     $this->command->shouldReceive('monitorQueueWorker');
//     $this->command->shouldReceive('getWatchDirectories')->andReturn([__DIR__]);
//     $this->command->shouldReceive('getLastModifiedTime')->andReturn(1000, 2000);

//     // Mock the info output
//     $this->command->shouldReceive('info')->with('Changes detected. Restarting queue worker...');

//     // Mock sleep and throw exception to break the loop
//     $this->command->shouldReceive('sleep')->andReturnUsing(function () {
//         throw new Exception('End test loop');
//     });

//     // Run the handle method and expect the exception to be thrown
//     // expect(fn() => $this->command->handle())->toThrow(Exception::class, 'End test loop');

// });

afterEach(function () {
    Mockery::close();
});
