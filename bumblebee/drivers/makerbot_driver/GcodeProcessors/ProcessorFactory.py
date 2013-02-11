from __future__ import absolute_import

import makerbot_driver


class ProcessorFactory(object):

    def __init__(self):
        pass

    def list_processors(self):
        pros = makerbot_driver.GcodeProcessors.all
        if 'errors' in pros:
            pros.remove('errors')
        return pros

    def create_processor_from_name(self, name):
        try:
            return getattr(makerbot_driver.GcodeProcessors, name)()
        except AttributeError:
            raise makerbot_driver.GcodeProcessors.ProcessorNotFoundError

    def process_list_with_commas(self, string):
        string = string.replace(' ', '')
        strings = string.split(',')
        for s in strings:
            if s == '':
                strings.remove(s)
        return strings

    def get_processors(self, processors):
        if isinstance(processors, str):
            processors = self.process_list_with_commas(processors)
        for processor in processors:
            yield self.create_processor_from_name(processor)
