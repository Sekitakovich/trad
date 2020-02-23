import signal
from datetime import datetime as dt
import time


def scheduler(arg1, args2):
    print(dt.now())

signal.signal(signal.SIGALRM, scheduler)
signal.setitimer(signal.ITIMER_REAL, 1, 1)

time.sleep(1000)
