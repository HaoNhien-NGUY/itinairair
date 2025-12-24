import { startStimulusApp } from '@symfony/stimulus-bundle';
import TextareaAutogrow from 'stimulus-textarea-autogrow';
import Dropdown from '@stimulus-components/dropdown';

const app = startStimulusApp();
app.register('textarea-autogrow', TextareaAutogrow);
app.register('dropdown', Dropdown);
