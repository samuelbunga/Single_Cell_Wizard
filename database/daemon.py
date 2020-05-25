import sys
import os
import time

"""
Description:
This is a daemon file which keeps running in the background
and checks with the database for any new job every 5 minutes
and process it.

Author: Samuel Bunga 
"""


def _dict_to_list(Dict):
    lst = []
    for keys, values in zip(Dict.keys(), Dict.values()):
        lst.append(keys)
        lst.append(values)
    return lst


def _job_check(converted_dict, job_id):
    if int(converted_dict['-job']) in job_id:
        return True
    elif int(converted_dict['-job']) not in job_id:
        return False


def _convert_dict(lst):
    res_dct = {lst[i]: lst[i + 1] for i in range(0, len(lst), 2)}
    return res_dct


def read_database(job_id):
    with open("database.txt", 'r+') as fh:
        for lines in fh:
            entry = lines.strip().split(" ")
            converted = _convert_dict(entry)
            job_check = _job_check(converted, job_id)
            dict_to_list(converted)
            if not job_check:
                print("processing the file with job ID", converted['-job'])
                cmd_str = " ".join(dict_to_list(converted))
                os.system("/usr/bin/perl "+cmd_str)
                job_id.append(int(converted['-job']))
    return job_id


if __name__ == '__main__':
    start = 'dont-stop'
    job_id = [12345]
    while start != "stop":
        if job_id:
            job_id = read_database(job_id)
        elif not job_id:
            read_database()
        time.sleep(30)