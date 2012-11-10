from __future__ import absolute_import, print_function

import os

import makerbot_driver


class ReturnObject(object):

    def __init__(self):
        pass


class MachineFactory(object):
    """This class is a factory for building machine drivers from
    a port connection. This class will take a connection, query it
    to verify it is a geunine 3d printer (or other device we can control)
    and build the appropritae machine type/version/etc from that.
    """
    def __init__(self, profile_dir=None):
        if profile_dir:
            self.profile_dir = profile_dir
        else:
            self.profile_dir = os.path.join(
                os.path.abspath(os.path.dirname(__file__)), 'profiles',)

    def create_inquisitor(self, portname):
        """
        This is made to ameliorate testing, this having to
        assign internal objects with <obj>.<internal_obj> = <obj> is a
        pain.
        """
        return MachineInquisitor(portname)

    def build_from_port(self, portname, leaveOpen=True):
        """
        Returns a tuple of an (s3gObj, ProfileObj)
        for a machine at port portname
        """
        machineInquisitor = self.create_inquisitor(portname)
        s3gBot, machine_setup_dict = machineInquisitor.query(leaveOpen)

        profile_regex = self.get_profile_regex(machine_setup_dict)
        matches = makerbot_driver.search_profiles_with_regex(
            profile_regex, self.profile_dir)
        matches = list(matches)
        return_object = ReturnObject
        attrs = ['s3g', 'profile', 'gcodeparser']
        for a in attrs:
            setattr(return_object, a, None)
        if len(matches) > 0:
            bestProfile = matches[0]
            setattr(return_object, 's3g', s3gBot)
            profile = makerbot_driver.Profile(bestProfile, self.profile_dir)
            profile.values['print_to_file_type']=[machine_setup_dict['print_to_file_type']]
            setattr(return_object, 'profile', profile)
            parser = makerbot_driver.Gcode.GcodeParser()
            parser.s3g = s3gBot
            parser.state.profile = getattr(return_object, 'profile')
            setattr(return_object, 'gcodeparser', parser)
        return return_object

    def create_s3g(self, portname):
        """
        This is made to ameliorate testing.  Otherwise we would
        not be able to reliably test the build_from_port function
        w/o being permanently attached to a specific port.
        """
        return makerbot_driver.s3g.from_filename(portname)

    def get_profile_regex(self, machine_setup_dict):
        """
        Decision tree for machine decisions.

        @param dict machine_setup_dict: A dictionary containing
          information about the connected machine
        @return str
        """
        regex = None
        #First check for VID/PID matches
        if 'vid' in machine_setup_dict and 'pid' in machine_setup_dict:
            regex = self.get_profile_regex_has_vid_pid(machine_setup_dict)
        if '.*Replicator2' == regex:
            if regex and machine_setup_dict.get('tool_count', 0) == 2:
                regex = regex + 'X'
            elif machine_setup_dict.get('tool_count', 0) != 1:
                regex = None
        else:
            if regex and machine_setup_dict.get('tool_count', 0) == 1:
                regex = regex + 'Single'
            elif regex and machine_setup_dict.get('tool_count', 0) == 2:
                regex = regex + 'Dual'
            else:
              regex = None
        return regex

    def get_profile_regex_has_vid_pid(self, machine_setup_dict):
        """If the machine has a VID and PID, we can assume it is part of
        the generation of machines that also have a tool_count.  We use the
        tool_count at the final criterion to narrow our search.
        """
        vid_pid_matches = []
        for machine in makerbot_driver.gMachineClasses.values():
            if machine['vid'] == machine_setup_dict['vid'] and machine['pid'] == machine_setup_dict['pid']:
                return machine['machineProfiles']
        return None


class MachineInquisitor(object):
    def __init__(self, portname):
        """ build a machine Inqusitor for an exact port"""
        self._portname = portname

    def create_s3g(self):
        """
        This is made to ameliorate testing, this having to
        assign internal objects with <obj>.<internal_obj> = <obj> is a
        pain.
        """
        return makerbot_driver.s3g.from_filename(self._portname)

    def query(self, leaveOpen=True):
        """
        open a connection to a machine and  query a machine for
        key settings needed to construct a machine from a profile

        @param leaveOpen IF true, serial connection to the machine is left open.
        @return a tuple of an (s3gObj, dictOfSettings
        """
        import makerbot_driver.s3g as s3g
        settings = {}
        s3gDriver = self.create_s3g()
        settings['vid'], settings['pid'] = s3gDriver.get_vid_pid()
        settings['tool_count'] = s3gDriver.get_toolhead_count()
        
        try:
            version_settings = s3gDriver.get_advanced_version();
            if version_settings['SoftwareVariant'] != 0:
                s3gDriver.set_print_to_file_type('x3g')
                settings['print_to_file_type'] = 'x3g'
            else: 
                s3gDriver.set_print_to_file_type('s3g')
                settings['print_to_file_type'] = 's3g'

        except makerbot_driver.CommandNotSupportedError:
            s3gDriver.set_print_to_file_type('s3g')
            settings['print_to_file_type'] = 's3g'
            
          
        if not leaveOpen:
            s3gDriver.close()
        return s3gDriver, settings
