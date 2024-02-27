# emuseum-wp

## Prototype Notes

Zack Rothauser developed this prototype to demonstrate the possibility of integrating WordPress and eMuseum. 

The idea was to create an experience of "related pages" that allows website visitors to transition seamlessly between the two platforms. And it worked! I recall a presentation where Zack demonstrated:

- Content editors working in the WordPress Dashboard can associate an eMuseum records with WordPress records.
- The public-facing WordPress site can display images, links, and other data from the associated eMuseum records.
- Additionally, a plain HTML page (standing in for an eMuseum page) can query WordPress for content related to a given object and output a block containing images, links, and other data.

That said, I don't know how completely this prototype captures everything Zack developed for that presentation. I also don't fully understand the limitations of the demo -- he may have delicately skirted around missing components. But I hope this existing code provides a starting point for this investigation.

(You will notice references in the code to "MoCP." That's the Museum of Contemporary Photography, Chicago. We should remove those references and replace them with a unique namespace.)
