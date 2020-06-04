
import argparse
import smtplib, ssl
import email, smtplib, ssl
from email import encoders
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText


parser          = argparse.ArgumentParser()
parser.add_argument('-to', dest='to', type=str)
parser.add_argument('-file', dest='file', type=str)
parser.add_argument('-linkout', dest='linkout', type=str)
args            = parser.parse_args()


port = 465  # For SSL
sender_email = "scwiz.tool@gmail.com"  # Enter your address
receiver_email = args.to  # Enter receiver address
password = ''
subject = "Job Completed"
body = "Please open the link below to download your results. \n"+args.linkout+"\n"+"Your link to the results will be expired in 24Hrs.\n"+"For any questions and inquiries, contact: help.scwiz.tool@gmail.com"

#message = """\
#Subject: Job Complteded
#This message is sent from Python."""

# Create a multipart message and set headers
message = MIMEMultipart()
message["From"] = sender_email
message["To"] = receiver_email
message["Subject"] = subject

# Add body to email
message.attach(MIMEText(body, "plain"))

# Add attachment to message and convert message to string
text = message.as_string()

context = ssl.create_default_context()
with smtplib.SMTP_SSL("smtp.gmail.com", port, context=context) as server:
    server.login(sender_email, password)
    server.sendmail(sender_email, receiver_email, text)
