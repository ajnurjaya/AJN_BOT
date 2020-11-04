#!/usr/bin/python
from selenium import webdriver
from PIL import Image

browser = webdriver.Chrome() 
browser.maximize_window() 
browser.get("http://10.35.105.112/MONITA/AREA01/") 
inputElement = browser.find_element_by_id("user") 
inputElement.send_keys("xxxx") #username
inputElement2 = browser.find_element_by_id("pass") 
inputElement2.send_keys("xxxx") #password 
button = browser.find_element_by_name("Submit") 
button.click() 
browser.get("http://10.35.105.112/MONITA/AREA01/c_frame/home_avaboard01?display=all") 
element = browser.find_element_by_xpath("//table[@style='border: 1px solid #fff;box-shadow: 0px 0px 20px #fff;']") 
location = element.location 
size = element.size 
browser.save_screenshot("C:/xampp/htdocs/Monita/tmp/monita.png") 
browser.quit() 
im = Image.open("C:/xampp/htdocs/Monita/tmp/monita.png") 
left = location['x'] 
top = location['y'] 
right = location['x'] + size['width'] 
bottom = location['y'] + size['height'] 
im = im.crop((left, top, right, bottom))
im.save('C:/xampp/htdocs/Monita/tmp/monita.png')
