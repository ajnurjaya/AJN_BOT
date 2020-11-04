#!/usr/bin/python
from selenium import webdriver
from PIL import Image
import time
import sys
import unittest
import util

browser = webdriver.Firefox() 
browser.maximize_window() 
browser.get("http://10.33.192.70/cacti/index.php") 
inputElement = browser.find_element_by_name("login_username") 
inputElement.send_keys("xxxx") #masukan username 
inputElement2 = browser.find_element_by_name("login_password") 
inputElement2.send_keys("xxxxx") #masukkan password 
button = browser.find_element_by_xpath("//input[@type='submit']") 
button.click() 
browser.get("http://10.33.192.70/cacti/graph_view.php?action=tree&tree_id=3&leaf_id=904")
element = browser.find_element_by_tag_name('Body')
location = element.location 
size = element.size 
time.sleep(3)
browser.save_screenshot("C:/xampp/htdocs/Monita/tmp/cacti.png") 
browser.quit() 
im = Image.open("C:/xampp/htdocs/Monita/tmp/cacti.png") 
left = location['x'] 
top = location['y'] 
right = location['x'] + size['width'] 
bottom = location['y'] + size['height'] 
im = im.crop((left, top, right, bottom))
im.save('C:/xampp/htdocs/Monita/tmp/cacti.png')