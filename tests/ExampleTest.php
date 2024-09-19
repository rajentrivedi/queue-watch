<?php

use QueueWatch\QueueWatch\Commands\QueueWatchCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use Illuminate\Console\Command;

beforeEach(function () {
    $this->commandForTest = new QueueWatchCommand();
    $this->command = Mockery::mock(QueueWatchCommand::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $this->command->shouldReceive('argument')->andReturn(null);
    $this->command->shouldReceive('option')->andReturn(null);
});

test('command signature is correct', function () {
    // dd($this->commandForTest->getName());
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
    $this->command->shouldReceive('info')->once()->with('Queue worker started.');
    
    $this->command->startQueueWorker();

    $processProperty = new ReflectionProperty($this->command, 'process');
    $processProperty->setAccessible(true);
    $process = $processProperty->getValue($this->command);

    expect($process)->toBeInstanceOf(Process::class);
});

test('stopQueueWorker stops the running process', function () {
    $mockProcess = Mockery::mock(Process::class);
    $mockProcess->shouldReceive('isRunning')->once()->andReturn(true);
    $mockProcess->shouldReceive('stop')->once();

    $processProperty = new ReflectionProperty($this->command, 'process');
    $processProperty->setAccessible(true);
    $processProperty->setValue($this->command, $mockProcess);

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

    $this->command->shouldReceive('error')->once()->with('Queue worker stopped unexpectedly. Restarting...');
    $this->command->shouldReceive('startQueueWorker')->once();

    $this->command->monitorQueueWorker();
});

test('handle method runs correctly when no directories to watch', function () {
    $this->command->shouldReceive('startQueueWorker')->once();
    $this->command->shouldReceive('monitorQueueWorker')->once();
    $this->command->shouldReceive('getWatchDirectories')->andReturn([]);
    $this->command->shouldReceive('error')->once()->with('No directories to watch. The queue worker will run without file watching.');

    $this->command->handle();
});

// test('handle method detects file changes and restarts worker', function () {
//     $this->command->shouldReceive('startQueueWorker')->twice();
//     $this->command->shouldReceive('stopQueueWorker')->once();
//     $this->command->shouldReceive('monitorQueueWorker')->twice();
//     $this->command->shouldReceive('getWatchDirectories')->andReturn([__DIR__]);
//     $this->command->shouldReceive('getLastModifiedTime')
//         ->andReturn(1000, 2000);
//     $this->command->shouldReceive('info')->once()->with('Changes detected. Restarting queue worker...');

//     // Mock sleep to prevent infinite loop
//     $this->command->shouldReceive('sleep')->once()->andReturnUsing(function () {
//         throw new Exception('End test loop');
//     });

//     expect(fn() => $this->command->handle())->toThrow(Exception::class, 'End test loop');
// });

afterEach(function () {
    Mockery::close();
});