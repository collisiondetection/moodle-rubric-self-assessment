# Description:
This is a plugin for moodle that allows students to perform a self assessment on any assignment that uses a rubric advanced grading grid.

Rubrics (marking grids) allow students to quickly see how their work will be graded, how marks have been awarded and how they can improve. They also speed up the process of awarding marks.

This plugin extends the builtin rubric features in moodle to allow students to use the same rubric that the teacher uses in order to self assess their work. Their self assessments are then visible to the teacher who grades their work which can speed up or inform the marking process as well as informing the teacher who is over/under confident with their work.

# Example
The image below shows what the teacher sees when grading student's work.
Coloured in green is the grade the teacher has chosen.
A green border shows what the student has said they think they're working at
![image](https://cloud.githubusercontent.com/assets/760604/23101310/8a9906b4-f688-11e6-924a-75a6a4171594.png)
Cells are coloured in pink if the teachers has changed a grade but hasn't yet pressed save: the pink cell shows what the previous grade was.

Both the student assessment and teacher assessment are saved into the gradebook and can be displayed as a raw score, percentage or grade:
![image](https://cloud.githubusercontent.com/assets/760604/23101338/14b4045c-f689-11e6-85ee-5da2a1a1046c.png)


# Install instructions:
Unzip the contents of this folder into your moodle folder. The 'grade' folder should be in the root level directory of your moodle install. 

All of the files for this module live in grade/grading/form/rubric and will overwrite any files in that location so make sure you take a backup of your moodle installation (files plus database) before experimenting with this plugin.

# Usage instructions
When you define or edit a rubric, you'll get an extra option for 'enable self assessment'.
This will allow students to click on the preview rubric to indicate the grade they think they're working at.
These self assessment grades will then be visible to the teacher when they grade the students work.

# Disclaimer
This module is in its early development stages and is provided with no guarantee.
