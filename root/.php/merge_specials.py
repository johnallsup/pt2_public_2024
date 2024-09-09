#!/usr/bin/env python
ptmd = open("ptmd_template.php").read()
block_specials = open("block_specials.php").read()
inline_specials = open("inline_specials.php").read()
lines = ptmd.splitlines()
out = []
blos = None
bloe = None
inls = None
inle = None
for i,line in enumerate(lines):
  if "### INLINE" in line:
    inls = i
  elif "### END INLINE" in line:
    inle = i
  elif "### BLOCK" in line:
    blos = i
  elif "### END BLOCK" in line:
    bloe = i
print(blos,bloe,inls,inle)
if None in [blos,bloe,inls,inle]:
  print(f"Can't find region") 
  exit()

lines[blos+1:bloe] = [block_specials]
lines[inls+1:inle] = [inline_specials]

with open("ptmd.php","wt") as f:
  print("\n".join(lines),file=f)
