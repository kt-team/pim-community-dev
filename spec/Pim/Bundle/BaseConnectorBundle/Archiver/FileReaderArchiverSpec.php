<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Job\Job;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Reader\File\CsvReader;
use Pim\Bundle\BaseConnectorBundle\Reader\File\FileReader;
use Prophecy\Argument;

class FileReaderArchiverSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\FileReaderArchiver');
    }

    function it_create_a_file_when_reader_is_valid(
        CsvReader $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        $filesystem
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);
        $reader->getFilePath()->willReturn('/tmp/tmp');

        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);

        $fs->write('tmp', '', true);

        $filesystem->write("type/alias/12/input/tmp", "", true)->shouldBeCalled();

        $this->archive($jobExecution);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        ItemReaderInterface $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        $filesystem
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $filesystem->write(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('input');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        AbstractStep $step,
        $filesystem
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);

        $filesystem->write(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_true_for_the_supported_job(
        FileReader $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $this->supports($jobExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
        ItemReaderInterface $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
